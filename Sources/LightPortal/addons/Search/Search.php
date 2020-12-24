<?php

namespace Bugo\LightPortal\Addons\Search;

use Bugo\LightPortal\Helpers;

/**
 * Search
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Search
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'fas fa-search';

	/**
	 * The IDs list of dark themes
	 *
	 * Список идентификаторов тёмных тем оформления
	 *
	 * @var string
	 */
	private $dark_themes = '';

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $modSettings, $context;

		$addSettings = [];
		if (!isset($modSettings['lp_search_addon_dark_themes']))
			$addSettings['lp_search_addon_dark_themes'] = $this->dark_themes;
		if (!empty($addSettings))
			updateSettings($addSettings);

		$context['lp_search_addon_dark_themes_options'] = Helpers::getForumThemes();

		$config_vars[] = array('multicheck', 'lp_search_addon_dark_themes');
	}

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_actions', __CLASS__ . '::actions#', false, __FILE__);
	}

	/**
	 * Add support for the ?action=portal;sa=search, and ?action=portal;sa=qsearch
	 *
	 * Добавляем поддержку действия ?action=portal;sa=search и ?action=portal;sa=qsearch
	 *
	 * @param array $actions
	 * @return void
	 */
	public function actions(array &$actions)
	{
		global $context;

		if (Helpers::request()->is('portal') && $context['current_subaction'] == 'qsearch')
			return call_user_func(array($this, 'prepareQuickResults'));

		if (Helpers::request()->is('portal') && $context['current_subaction'] == 'search')
			return call_user_func(array($this, 'showResults'));
	}

	/**
	 * Process the search and display the results
	 *
	 * Обрабатываем поиск и выводим результаты
	 *
	 * @return void
	 */
	public function showResults()
	{
		global $context, $txt, $scripturl;

		$context['page_title']     = $txt['lp_block_types']['search'];
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$context['search_results'] = $this->getResults();

		require_once(__DIR__ . '/Template.php');

		$context['sub_template'] = 'show_results';

		obExit();
	}

	/**
	 * Display quick search results as a JSON string
	 *
	 * Отображаем результаты быстрого поиска, в виде строки JSON
	 *
	 * @return void
	 */
	private function prepareQuickResults()
	{
		global $smcFunc, $context, $scripturl;

		if (Helpers::request()->filled('phrase') === false)
			return;

		$query = $smcFunc['htmltrim']($smcFunc['htmlspecialchars'](Helpers::request('phrase')));

		if (empty($query))
			return;

		exit(json_encode($this->query($query)));
	}

	/**
	 * Get the results of a regular search, as an array
	 *
	 * Получаем результаты обычного поиска, в виде массива
	 *
	 * @return array
	 */
	private function getResults()
	{
		global $smcFunc;

		if (Helpers::request()->filled('search') === false)
			return [];

		$query = $smcFunc['htmltrim']($smcFunc['htmlspecialchars'](Helpers::request('search')));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	/**
	 * Make a query to the database and get the results as an array
	 *
	 * Делаем запрос к базе данных и получаем результаты в виде массива
	 *
	 * @param string $query
	 * @return array
	 */
	private function query(string $query)
	{
		global $smcFunc, $context, $scripturl, $txt;

		$title_words = explode(' ', $query);

		$search_formula = '';
		foreach ($title_words as $key => $word) {
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) = lower(\'' . $word . '\') THEN ' . (count($title_words) - $key) * 5 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 4 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.content) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 3 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.alias) = lower(\'' . $word . '\') THEN ' . (count($title_words) - $key) * 2 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 1 . ' ELSE 0 END';
		}

		$request = $smcFunc['db_query']('', '
			SELECT p.alias, p.content, p.type, GREATEST(p.created_at, p.updated_at) AS date, (' . $search_formula . ') AS related, t.title, mem.id_member, mem.real_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.lang = {string:current_lang})
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE (' . $search_formula . ') > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY related DESC
			LIMIT 10',
			array(
				'current_lang' => $context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions()
			)
		);

		$results = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))	{
			Helpers::parseContent($row['content'], $row['type']);

			$results[] = array(
				'link'    => $scripturl . '?page=' . $row['alias'],
				'title'   => $row['title'],
				'content' => Helpers::getTeaser(strip_tags($row['content'])),
				'author'  => empty($row['id_member']) ? $txt['guest'] : ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => Helpers::getFriendlyTime($row['date'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $results;
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time)
	{
		global $modSettings, $scripturl, $context, $txt, $settings;

		if ($type !== 'search')
			return;

		loadCSSFile('https://cdn.jsdelivr.net/npm/easy-autocomplete@1/dist/easy-autocomplete.min.css', array('external' => true));
		loadCSSFile('https://cdn.jsdelivr.net/npm/easy-autocomplete@1/dist/easy-autocomplete.themes.min.css', array('external' => true));
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/easy-autocomplete@1/dist/jquery.easy-autocomplete.min.js', array('external' => true));

		$dark_themes = !empty($modSettings['lp_search_addon_dark_themes']) ? json_decode($modSettings['lp_search_addon_dark_themes'], true) : [];

		ob_start();

		echo '
		<form class="search_addon centertext" action="', $scripturl, '?action=portal;sa=search" method="post" accept-charset="', $context['character_set'], '">
			<input type="search" name="search">
		</form>
		<script>
			jQuery(document).ready(function ($) {
				let easyAutoOptions = {
					url: function (phrase) {
						return "', $scripturl, '?action=portal;sa=qsearch"
					},
					getValue: function (element) {
						return element.title;
					},
					ajaxSettings: {
						dataType: "json",
						method: "POST",
						data: {
							dataType: "json"
						}
					},
					preparePostData: function (data) {
						data.phrase = $(".search_addon input").val();
						return data;
					},
					requestDelay: 500,
					template: {
						type: "links",
						fields: {
							link: "link"
						}
					},
					list: {
						match: {
							enabled: true
						}
					},
					placeholder: "', $txt['lp_block_types']['search'], '",
					adjustWidth: false,
					theme: "' . (!empty($dark_themes) && !empty($dark_themes[$settings['theme_id']]) ? 'dark' : 'blue-light') . '"
				};
				$(".search_addon input").easyAutocomplete(easyAutoOptions);
			});
		</script>';

		$content = ob_get_clean();
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'EasyAutocomplete',
			'link' => 'https://github.com/pawelczak/EasyAutocomplete',
			'author' => 'Łukasz Pawełczak',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/pawelczak/EasyAutocomplete/blob/master/LICENSE.txt'
			)
		);
	}
}

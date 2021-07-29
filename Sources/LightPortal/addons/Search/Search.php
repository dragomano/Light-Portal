<?php

namespace Bugo\LightPortal\Addons\Search;

use Bugo\LightPortal\{Helpers, Plugin};

/**
 * Search
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Search extends Plugin
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-search';

	/**
	 * @var int
	 */
	private $min_chars = 3;

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_actions', __CLASS__ . '::actions#', false, __FILE__);
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $modSettings;

		$addSettings = [];
		if (!isset($modSettings['lp_search_addon_min_chars']))
			$addSettings['lp_search_addon_min_chars'] = $this->min_chars;
		if (!empty($addSettings))
			updateSettings($addSettings);

		$config_vars[] = array('int', 'lp_search_addon_min_chars');
	}

	/**
	 * Add support for the "?action=portal;sa=search", and "?action=portal;sa=qsearch"
	 *
	 * Добавляем поддержку действия "?action=portal;sa=search" и "?action=portal;sa=qsearch"
	 *
	 * @return void
	 */
	public function actions()
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
		global $context, $txt;

		$context['page_title']     = $txt['lp_block_types']['search'];
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$context['search_results'] = $this->getResults();

		$this->loadTemplate(__DIR__);

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
		global $smcFunc;

		$data = Helpers::request()->json();

		if (empty($data['phrase']))
			return;

		$query = $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($data['phrase']));

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
				'content' => Helpers::getTeaser($row['content']),
				'author'  => empty($row['id_member']) ? $txt['guest'] : ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => Helpers::getFriendlyTime($row['date'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $results;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public function prepareContent(&$content, $type)
	{
		global $scripturl, $context, $txt, $modSettings;

		if ($type !== 'search')
			return;

		loadCSSFile('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css', array('external' => true));
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js', array('external' => true));

		ob_start();

		echo '
		<form class="search_addon centertext" action="', $scripturl, '?action=portal;sa=search" method="post" accept-charset="', $context['character_set'], '">
			<input type="search" name="search" placeholder="', $txt['lp_block_types']['search'], '">
		</form>
		<script>
			new autoComplete({
				selector: ".search_addon input",' . (!empty($modSettings['lp_search_addon_min_chars']) ? '
				minChars: ' . $modSettings['lp_search_addon_min_chars'] . ',' : '') . '
				source: async function(term, response) {
					const results = await fetch("', $scripturl, '?action=portal;sa=qsearch", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							phrase: term
						})
					});

					if (results.ok) {
						const data = await results.json();
						response(data);
					}
				},
				renderItem: function (item, search) {
					search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
					let re = new RegExp("(" + search.split(" ").join("|") + ")", "gi");

					return `<div class="autocomplete-suggestion" data-val="` + item.title + `" data-link="` + item.link + `" style="cursor: pointer">` + item.title.replace(re, "<b>$1</b>") + `</div>`;
				},
				onSelect: function(e, term, item) {
					window.location = item.dataset.link;
				}
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
			'title' => 'Vanilla JavaScript autoComplete',
			'link' => 'https://github.com/Pixabay/JavaScript-autoComplete',
			'author' => 'Simon Steinberger / Pixabay.com',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://www.opensource.org/licenses/mit-license.php'
			)
		);
	}
}

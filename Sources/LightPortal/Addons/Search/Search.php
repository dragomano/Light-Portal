<?php

/**
 * Search.php
 *
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\Search;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class Search extends Plugin
{
	public string $icon = 'fas fa-search';

	public function init()
	{
		add_integration_function('integrate_actions', __CLASS__ . '::actions#', false, __FILE__);
	}

	public function addSettings(array &$config_vars)
	{
		global $modSettings;

		$addSettings = [];
		if (! isset($modSettings['lp_search_addon_min_chars']))
			$addSettings['lp_search_addon_min_chars'] = 3;
		if (! empty($addSettings))
			updateSettings($addSettings);

		$config_vars['search'][] = array('int', 'min_chars');
	}

	public function actions()
	{
		global $context;

		if (Helper::request()->is(LP_ACTION) && $context['current_subaction'] === 'qsearch')
			return call_user_func(array($this, 'prepareQuickResults'));

		if (Helper::request()->is(LP_ACTION) && $context['current_subaction'] === 'search')
			return call_user_func(array($this, 'showResults'));
	}

	public function showResults()
	{
		global $context, $txt;

		$context['page_title']     = $txt['lp_search']['title'];
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$context['search_results'] = $this->getResults();

		$this->loadTemplate();

		$context['sub_template'] = 'show_results';

		obExit();
	}

	private function prepareQuickResults()
	{
		global $smcFunc;

		$data = Helper::request()->json();

		if (empty($data['phrase']))
			return;

		$query = $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($data['phrase']));

		exit(json_encode($this->query($query)));
	}

	private function getResults(): array
	{
		global $smcFunc;

		if (Helper::request()->notEmpty('search') === false)
			return [];

		$query = $smcFunc['htmltrim']($smcFunc['htmlspecialchars'](Helper::request('search')));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	private function query(string $query): array
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
				'permissions'  => Helper::getPermissions()
			)
		);

		$results = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))	{
			Helper::parseContent($row['content'], $row['type']);

			$results[] = array(
				'link'    => $scripturl . '?' . LP_PAGE_PARAM . '=' . $row['alias'],
				'title'   => $row['title'],
				'content' => Helper::getTeaser($row['content']),
				'author'  => empty($row['id_member']) ? $txt['guest'] : ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => Helper::getFriendlyTime($row['date'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $results;
	}

	public function prepareContent(string $type)
	{
		global $scripturl, $context, $txt, $modSettings;

		if ($type !== 'search')
			return;

		loadCSSFile('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css', array('external' => true));
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js', array('external' => true));

		echo '
		<form class="search_addon centertext" action="', $scripturl, '?action=', LP_ACTION, ';sa=search" method="post" accept-charset="', $context['character_set'], '">
			<input type="search" name="search" placeholder="', $txt['lp_search']['title'], '">
		</form>
		<script>
			new autoComplete({
				selector: ".search_addon input",' . (empty($modSettings['lp_search_addon_min_chars']) ? '' : '
				minChars: ' . $modSettings['lp_search_addon_min_chars'] . ',') . '
				source: async function(term, response) {
					const results = await fetch("', $scripturl, '?action=', LP_ACTION, ';sa=qsearch", {
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
	}

	public function credits(array &$links)
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

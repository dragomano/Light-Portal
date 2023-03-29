<?php

/**
 * Search.php
 *
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.03.23
 */

namespace Bugo\LightPortal\Addons\Search;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Search extends Plugin
{
	public string $icon = 'fas fa-search';

	public function init()
	{
		$this->applyHook('actions');
	}

	public function addSettings(array &$config_vars)
	{
		$this->addDefaultValues([
			'min_chars' => 3,
		]);

		$config_vars['search'][] = ['int', 'min_chars'];
	}

	public function actions()
	{
		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'qsearch')
			return call_user_func([$this, 'prepareQuickResults']);

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'search')
			return call_user_func([$this, 'showResults']);
	}

	public function showResults()
	{
		$this->context['page_title']     = $this->txt['lp_search']['title'];
		$this->context['robot_no_index'] = true;

		$this->context['linktree'][] = [
			'name' => $this->context['page_title']
		];

		$this->context['search_results'] = $this->getResults();

		$this->setTemplate('show_results');

		$this->obExit();
	}

	private function prepareQuickResults()
	{
		$data = $this->request()->json();

		if (empty($data['phrase']))
			return;

		$query = $this->smcFunc['htmltrim']($this->smcFunc['htmlspecialchars']($data['phrase']));

		exit(json_encode($this->query($query)));
	}

	private function getResults(): array
	{
		if ($this->request()->isNotEmpty('search') === false)
			return [];

		$query = $this->smcFunc['htmltrim']($this->smcFunc['htmlspecialchars']($this->request('search')));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	private function query(string $query): array
	{
		$title_words = explode(' ', $query);

		$search_formula = '';
		foreach ($title_words as $key => $word) {
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) = lower(\'' . $word . '\') THEN ' . (count($title_words) - $key) * 5 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 4 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.content) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) * 3 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.alias) = lower(\'' . $word . '\') THEN ' . (count($title_words) - $key) * 2 . ' ELSE 0 END';
			$search_formula .= ($search_formula ? ' + ' : '') . 'CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($title_words) - $key) . ' ELSE 0 END';
		}

		$request = $this->smcFunc['db_query']('', '
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
			[
				'current_lang' => $this->context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions()
			]
		);

		$results = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request))	{
			$row['content'] = parse_content($row['content'], $row['type']);

			$results[] = [
				'link'    => LP_PAGE_URL . $row['alias'],
				'title'   => $row['title'],
				'content' => $this->getTeaser($row['content']),
				'author'  => empty($row['id_member']) ? $this->txt['guest'] : ('<a href="' . $this->scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => $this->getFriendlyTime($row['date'])
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $results;
	}

	public function prepareContent(string $type)
	{
		if ($type !== 'search')
			return;

		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js');

		echo '
		<form class="search_addon centertext" action="', LP_BASE_URL, ';sa=search" method="post" accept-charset="', $this->context['character_set'], '">
			<input type="search" name="search" placeholder="', $this->txt['lp_search']['title'], '">
		</form>
		<script>
			new autoComplete({
				selector: ".search_addon input",' . (empty($this->context['lp_search_plugin']['min_chars']) ? '' : '
				minChars: ' . $this->context['lp_search_plugin']['min_chars'] . ',') . '
				source: async function(term, response) {
					const results = await fetch("', LP_BASE_URL, ';sa=qsearch", {
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
		$links[] = [
			'title' => 'Vanilla JavaScript autoComplete',
			'link' => 'https://github.com/Pixabay/JavaScript-autoComplete',
			'author' => 'Simon Steinberger / Pixabay.com',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://www.opensource.org/licenses/mit-license.php'
			]
		];
	}
}

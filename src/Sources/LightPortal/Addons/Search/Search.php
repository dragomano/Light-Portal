<?php

/**
 * Search.php
 *
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 21.03.24
 */

namespace Bugo\LightPortal\Addons\Search;

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Utils\{Content, DateTime};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class Search extends Block
{
	public string $icon = 'fas fa-search';

	public function init(): void
	{
		$this->applyHook('actions');
	}

	public function addSettings(array &$settings): void
	{
		$this->addDefaultValues([
			'min_chars' => 3,
		]);

		$settings['search'][] = ['range', 'min_chars', 'min' => 1, 'max' => 10];
	}

	public function actions()
	{
		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'qsearch')
			return call_user_func([$this, 'prepareQuickResults']);

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'search')
			return call_user_func([$this, 'showResults']);

		return false;
	}

	/**
	 * @throws IntlException
	 */
	public function showResults(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_search']['title'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title']
		];

		Utils::$context['search_results'] = $this->getResults();

		$this->setTemplate('show_results');

		Utils::obExit();
	}

	/**
	 * @throws IntlException
	 */
	private function prepareQuickResults(): void
	{
		$data = $this->request()->json();

		if (empty($data['phrase']))
			return;

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($data['phrase']));

		exit(json_encode($this->query($query)));
	}

	/**
	 * @throws IntlException
	 */
	private function getResults(): array
	{
		if ($this->request()->isNotEmpty('search') === false)
			return [];

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($this->request('search')));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	/**
	 * @throws IntlException
	 */
	private function query(string $query): array
	{
		$titleWords = explode(' ', $query);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.title) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 5 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 4 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.content) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 3 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.alias) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) . ' ELSE 0 END';
		}

		$result = Utils::$smcFunc['db_query']('', '
			SELECT p.alias, p.content, p.type, GREATEST(p.created_at, p.updated_at) AS date, (' . $searchFormula . ') AS related, t.title, mem.id_member, mem.real_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:current_lang})
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY related DESC
			LIMIT 10',
			[
				'current_lang' => Utils::$context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result))	{
			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[] = [
				'link'    => LP_PAGE_URL . $row['alias'],
				'title'   => $row['title'],
				'content' => $this->getTeaser($row['content']),
				'author'  => empty($row['id_member'])
					? Lang::$txt['guest']
					: ('<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => DateTime::relative($row['date']),
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	public function prepareAssets(array &$assets): void
	{
		$assets['css']['search'][]     = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css';
		$assets['scripts']['search'][] = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js';
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'search')
			return;

		Theme::loadCSSFile('light_portal/search/auto-complete.css');
		Theme::loadJavaScriptFile('light_portal/search/auto-complete.min.js', ['minimize' => true]);

		echo '
		<form class="search_addon centertext" action="', LP_BASE_URL, ';sa=search" method="post" accept-charset="', Utils::$context['character_set'], '">
			<input type="search" name="search" placeholder="', Lang::$txt['lp_search']['title'], /** @lang text */ '">
		</form>
		<script>
			new autoComplete({
				selector: ".search_addon input",', (empty(Utils::$context['lp_search_plugin']['min_chars']) ? '' : '
				minChars: ' . Utils::$context['lp_search_plugin']['min_chars'] . ','), '
				source: async function(term, response) {
					const results = await fetch("', LP_BASE_URL, /** @lang text */ ';sa=qsearch", {
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

	public function credits(array &$links): void
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

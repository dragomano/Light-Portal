<?php declare(strict_types=1);

/**
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.12.24
 */

namespace Bugo\LightPortal\Plugins\Search;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class Search extends Block
{
	public string $icon = 'fas fa-search';

	public function init(): void
	{
		$this->applyHook(Hook::actions);
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'min_chars' => 3,
		]);

		$e->args->settings[$this->name][] = ['range', 'min_chars', 'min' => 1, 'max' => 10];
	}

	public function actions()
	{
		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'qsearch')
			return call_user_func($this->prepareQuickResults(...));

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === $this->name)
			return call_user_func($this->showResults(...));

		return false;
	}

	public function showResults(): void
	{
		Utils::$context['page_title'] = $this->txt['title'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title']
		];

		Utils::$context['search_results'] = $this->getResults();

		$this->setTemplate()->withSubTemplate('show_results');

		Utils::obExit();
	}

	private function prepareQuickResults(): void
	{
		$data = $this->request()->json();

		if (empty($data['phrase']))
			return;

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($data['phrase']));

		exit(json_encode($this->query($query)));
	}

	private function getResults(): array
	{
		if ($this->request()->isNotEmpty($this->name) === false)
			return [];

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($this->request($this->name)));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	private function query(string $query): array
	{
		$titleWords = explode(' ', $query);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.value) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 5 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.value) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 4 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.content) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 3 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.slug) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.slug) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) . ' ELSE 0 END';
		}

		$result = Db::$db->query('', '
			SELECT p.slug, p.content, p.type, GREATEST(p.created_at, p.updated_at) AS date, (' . $searchFormula . ') AS related, t.value, mem.id_member, mem.real_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:current_lang})
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND p.deleted_at = 0
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY related DESC
			LIMIT 10',
			[
				'current_lang' => Utils::$context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Permission::all(),
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result))	{
			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[] = [
				'link'    => LP_PAGE_URL . $row['slug'],
				'title'   => $row['value'],
				'content' => Str::getTeaser($row['content']),
				'author'  => empty($row['id_member'])
					? Lang::$txt['guest']
					: ('<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => DateTime::relative((int) $row['date']),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function prepareAssets(Event $e): void
	{
		$e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css';
		$e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js';
	}

	public function prepareContent(): void
	{
		Theme::loadCSSFile('light_portal/search/auto-complete.css');
		Theme::loadJavaScriptFile('light_portal/search/auto-complete.min.js', ['minimize' => true]);

		echo '
		<form class="search_addon centertext" action="', LP_BASE_URL, ';sa=search" method="post" accept-charset="', Utils::$context['character_set'], '">
			<input type="search" name="search" placeholder="', $this->txt['title'], /** @lang text */ '">
		</form>
		<script>
			new autoComplete({
				selector: ".search_addon input",', (empty($this->context['min_chars']) ? '' : '
				minChars: ' . $this->context['min_chars'] . ','), '
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

	public function credits(Event $e): void
	{
		$e->args->links[] = [
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

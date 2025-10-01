<?php declare(strict_types=1);

/**
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\Search;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ForumHook;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-search')]
class Search extends Block
{
	use HasView;

	#[HookAttribute(PortalHook::addSettings)]
	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'min_chars' => 3,
		]);

		$e->args->settings[$this->name][] = ['range', 'min_chars', 'min' => 1, 'max' => 10];
	}

	#[HookAttribute(PortalHook::prepareAssets)]
	public function prepareAssets(Event $e): void
	{
		$e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.css';
		$e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/pixabay-javascript-autocomplete@1/auto-complete.min.js';
	}

	#[HookAttribute(PortalHook::prepareContent)]
	public function prepareContent(): void
	{
		Theme::loadCSSFile('light_portal/search/auto-complete.css');
		Theme::loadJavaScriptFile('light_portal/search/auto-complete.min.js', ['minimize' => true]);

		echo $this->view('form', [
			'baseUrl' => LP_BASE_URL,
			'plugin'  => $this,
		]);
	}

	#[HookAttribute(PortalHook::credits)]
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

	#[HookAttribute(PortalHook::init)]
	public function init(): void
	{
		$this->applyHook(ForumHook::actions);
	}

	public function actions()
	{
		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'qsearch') {
			return call_user_func($this->prepareQuickResults(...));
		}

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === $this->name) {
			return call_user_func($this->showResults(...));
		}

		return false;
	}

	public function showResults(): void
	{
		Utils::$context['page_title'] = $this->txt['title'];

		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()->add(Utils::$context['page_title']);

		Utils::$context['search_results'] = $this->getResults();

		$this->useCustomTemplate();

		Utils::obExit();
	}

	private function prepareQuickResults(): void
	{
		$data = $this->request()->json();

		if (empty($data['phrase']))
			return;

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($data['phrase']));

		$this->response()->exit($this->query($query));
	}

	private function getResults(): array
	{
		if ($this->request()->isNotEmpty($this->name) === false)
			return [];

		$query = Utils::$smcFunc['htmltrim'](Utils::htmlspecialchars($this->request()->get($this->name)));

		if (empty($query))
			return [];

		return $this->query($query);
	}

	private function query(string $query): array
	{
		$titleWords = explode(' ', $query);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$word = htmlentities($word);

			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.title) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 5 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 4 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(t.content) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) * 3 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.slug) = lower(\'' . $word . '\') THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE WHEN lower(p.slug) LIKE lower(\'%' . $word . '%\') THEN ' . (count($titleWords) - $key) . ' ELSE 0 END';
		}

		$result = Db::$db->query('
			SELECT
				p.slug, p.type, GREATEST(p.created_at, p.updated_at) AS date,
				(' . $searchFormula . ') AS related, mem.id_member, mem.real_name,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title,
				COALESCE(t.content, tf.content, {string:empty_string}) AS content
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:current_lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND p.deleted_at = 0
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY related DESC
			LIMIT 10',
			[
				'empty_string'  => '',
				'current_lang'  => Utils::$context['user']['language'],
				'fallback_lang' => Config::$language,
				'status'        => 1,
				'current_time'  => time(),
				'permissions'   => Permission::all(),
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);
			Lang::censorText($row['content']);

			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[] = [
				'link'    => LP_PAGE_URL . $row['slug'],
				'author'  => empty($row['id_member'])
					? Lang::$txt['guest']
					: ('<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'),
				'date'    => DateTime::relative((int) $row['date']),
				'title'   => $row['title'],
				'content' => Str::getTeaser($row['content']),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}

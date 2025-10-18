<?php declare(strict_types=1);

/**
 * @package Search (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\Search;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\ForumHook;
use LightPortal\Enums\Permission;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\Content;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasView;
use LightPortal\Utils\Traits\HasTranslationJoins;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-search')]
class Search extends Block
{
	use HasView;
	use HasTranslationJoins;

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'min_chars' => 3,
		]);

		$e->args->settings[$this->name][] = ['range', 'min_chars', 'min' => 1, 'max' => 10];
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

		echo $this->view('form', [
			'baseUrl' => LP_BASE_URL,
			'plugin'  => $this,
		]);
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

			$searchFormula .= ($searchFormula ? ' + ' : '')
				. 'CASE WHEN lower(t.title) = lower(\'' . $word . '\') THEN '
				. (count($titleWords) - $key) * 5 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '')
				. 'CASE WHEN lower(t.title) LIKE lower(\'%' . $word . '%\') THEN '
				. (count($titleWords) - $key) * 4 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '')
				. 'CASE WHEN lower(t.content) LIKE lower(\'%' . $word . '%\') THEN '
				. (count($titleWords) - $key) * 3 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '')
				. 'CASE WHEN lower(p.slug) = lower(\'' . $word . '\') THEN '
				. (count($titleWords) - $key) * 2 . ' ELSE 0 END';
			$searchFormula .= ($searchFormula ? ' + ' : '')
				. 'CASE WHEN lower(p.slug) LIKE lower(\'%' . $word . '%\') THEN '
				. (count($titleWords) - $key) . ' ELSE 0 END';
		}

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'slug', 'type',
				'date'    => new Expression('GREATEST(p.created_at, p.updated_at)'),
				'related' => new Expression('(' . $searchFormula . ')'),
			])
			->join(
				['mem' => 'members'],
				'p.author_id = mem.id_member',
				['id_member', 'real_name'],
				Select::JOIN_LEFT
			)
			->where(new Expression('(' . $searchFormula . ') > 0'))
			->where([
				'status'          => 1,
				'deleted_at'      => 0,
				'created_at <= ?' => time(),
				'p.entry_type'    => EntryType::DEFAULT->name(),
				'permissions'     => Permission::all(),
			])
			->order('related DESC')
			->limit(10);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content']]);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);
			Lang::censorText($row['content']);

			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[] = [
				'link'    => LP_PAGE_URL . $row['slug'],
				'author'  => $this->getLink($row),
				'date'    => DateTime::relative($row['date']),
				'title'   => $row['title'],
				'content' => Str::getTeaser($row['content']),
			];
		}

		return $items;
	}

	private function getLink(array $row): string
	{
		if (empty($row['id_member']))
			return Lang::$txt['guest'];

		return '<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
	}
}

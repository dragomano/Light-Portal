<?php declare(strict_types=1);

/**
 * @package FavoritePages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.26
 */

namespace LightPortal\Plugins\FavoritePages;

use Bugo\Bricks\Tables\DateColumn;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\ForumHook;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\Tables\NumViewsColumn;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\Str;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-bookmark')]
class FavoritePages extends Plugin
{
	private readonly PageRepositoryInterface $pageRepository;

	public function __construct()
	{
		parent::__construct();

		$this->pageRepository = app(PageRepositoryInterface::class);
	}

	public function init(): void
	{
		if (empty(User::$me->allowedTo('light_portal_view')))
			return;

		$this->applyHook(ForumHook::profileAreas);
		$this->applyHook(ForumHook::profilePopup);
	}

	public function afterPageContent(): void
	{
		if (empty(Utils::$context['user']['is_logged']))
			return;

		$pageId = Utils::$context['lp_page']['id'] ?? 0;

		if (empty($pageId))
			return;

		$isFavorite = in_array($pageId, $this->getFavoritePageIds(), true);

		$label = $isFavorite ? $this->txt['remove'] : $this->txt['add'];
		$icon  = $isFavorite ? 'fas fa-bookmark' : 'far fa-bookmark';

		$link = Str::html('a', [
				'class' => 'button',
				'href'  => Utils::$context['canonical_url'] . ';lp_favorite=' . $pageId,
			])
			->addHtml(Str::html('i', ['class' => $icon]))
			->addHtml(' ' . $label);

		$wrapper = Str::html('div', ['class' => 'lp_favorite_pages_toggle descbox centertext'])
			->addHtml($link);

		echo $wrapper;
	}

	public function preparePageData(Event $e): void
	{
		if (! $this->request()->has('lp_favorite'))
			return;

		$pageId = (int) $e->args->data['id'];

		if ($pageId < 1)
			return;

		$this->toggleFavoritePage($pageId);
	}

	public function profileAreas(array &$areas): void
	{
		$areas['info']['areas']['favorite_pages'] = [
			'label'      => $this->txt['menu_item_title'],
			'function'   => self::class . '::showFavoritePages#',
			'icon'       => 'like',
			'enabled'    => User::$me->allowedTo('light_portal_view'),
			'permission' => [
				'own' => 'light_portal_view',
			],
		];
	}

	public function profilePopup(array &$items): void
	{
		$counter = 0;
		foreach ($items as $item) {
			$counter++;

			if ($item['area'] === 'showdrafts')
				break;
		}

		$items = array_merge(
			array_slice($items, 0, $counter, true),
			[
				[
					'menu'  => 'info',
					'area'  => 'favorite_pages',
					'title' => Str::html('span', [
							'class' => 'lp_menu_label',
						])
						->addText($this->txt['menu_item_title']),
				]
			],
			array_slice($items, $counter, null, true)
		);
	}

	public function showFavoritePages(int $memID): void
	{
		Utils::$context['current_member'] = $memID;

		Utils::$context['page_title'] = Utils::$context['forum_name'] . ' - ' . $this->txt['menu_item_title'];

		$ids = $this->getFavoritePageIds($memID);

		$params = ['list', ['p.page_id' => $ids ?: [0]]];

		$builder = PortalTableBuilder::make('user_favorite_pages', $this->txt['entries'])
			->withParams(
				30,
				noItemsLabel: $this->txt['none'],
				action: Config::$scripturl . '?action=profile;area=favorite_pages;u=' . $memID,
				defaultSortColumn: 'date'
			)
			->setItems($this->pageRepository->getAll(...), $params)
			->setCount($this->pageRepository->getTotalCount(...), $params)
			->addColumns([
				IdColumn::make()->setSort('p.page_id'),
				DateColumn::make(title: __('date')),
				NumViewsColumn::make(),
				TitleColumn::make()
					->setData(static fn($entry) => Str::html('a', ['class' => 'bbc_link'])
						->href(LP_PAGE_URL . $entry['slug'])
						->setText($entry['title']), 'word_break'
					),
			]);

		app(TablePresenterInterface::class)->show($builder);
	}

	private function getFavoritePageIds(?int $memberId = null): array
	{
		$memberId ??= User::$me->id;

		return $this->userCache($this->name . '_ids')
			->setFallback(fn() => $this->loadFavoritePageIds($memberId));
	}

	private function loadFavoritePageIds(int $memberId): array
	{
		$select = $this->sql->select()
			->from('lp_params')
			->columns(['value'])
			->where([
				'item_id' => $memberId,
				'type'    => 'member',
				'name'    => $this->name,
			])
			->limit(1);

		$result = $this->sql->execute($select);

		$row = $result->current();

		return array_map('intval', json_decode($row['value'] ?? '', true) ?? []);
	}

	private function toggleFavoritePage(int $pageId): void
	{
		$ids = $this->getFavoritePageIds();

		if (in_array($pageId, $ids, true)) {
			$ids = array_values(array_diff($ids, [$pageId]));
		} else {
			$ids[] = $pageId;
		}

		$replace = $this->sql->replace('lp_params')
			->setConflictKeys(['item_id', 'type', 'name'])
			->values([
				'item_id' => User::$me->id,
				'type'    => 'member',
				'name'    => $this->name,
				'value'   => json_encode(array_values($ids)),
			]);

		$this->sql->execute($replace);

		$this->userCache($this->name . '_ids')->forget();
	}
}

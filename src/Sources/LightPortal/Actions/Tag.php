<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Lists\TagList;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasRequest;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	use HasRequest;

	public function show(): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$tag = [
			'id' => Str::typed('int', $this->request()->get('id'))
		];

		$tags = app(TagList::class)();
		if (array_key_exists($tag['id'], $tags) === false) {
			Utils::$context['error_link'] = PortalSubAction::TAGS->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		$tag = $tags[$tag['id']];
		Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_tags_by_key'], $tag['title']);
		Utils::$context['canonical_url'] = PortalSubAction::TAGS->url() . ';id=' . $tag['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_page_tags'], PortalSubAction::TAGS->url())
			->add($tag['title']);

		$this->cardList->show($this);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		return $this->getPreparedResults(
			$this->repository->getPagesByTag((int) $this->request()->get('id'), $start, $limit, $sort)
		);
	}

	public function getTotalPages(): int
	{
		return $this->repository->getTotalPagesByTag((int) $this->request()->get('id'));
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_page_tags'];
		Utils::$context['canonical_url']  = PortalSubAction::TAGS->url();
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()->add(Utils::$context['page_title']);

		$this->getTablePresenter()->show(
			PortalTableBuilder::make('tags', Utils::$context['page_title'])
				->withParams(
					Setting::get('defaultMaxListItems', 'int', 50),
					Lang::$txt['lp_no_tags'],
					Utils::$context['canonical_url'],
					'value'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					Column::make('value', Lang::$txt['lp_tag_column'])
						->setData(static fn($entry) => implode('', [
							$entry['icon'] . ' ',
							Str::html('a', $entry['title'])
								->href($entry['link']),
						]))
						->setSort('title DESC', 'title'),
					Column::make('frequency', Lang::$txt['lp_frequency_column'])
						->setData('frequency', 'centertext')
						->setSort('frequency DESC', 'frequency'),
				])
		);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'title'): array
	{
		return $this->repository->getTagsWithPageCount($start, $limit, $sort);
	}

	public function getTotalCount(): int
	{
		return $this->repository->getTotalTagsWithPages();
	}
}

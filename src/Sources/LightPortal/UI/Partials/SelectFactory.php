<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Partials;

use LightPortal\Lists\CategoryList;
use LightPortal\Lists\PageList;
use LightPortal\Lists\TagList;
use InvalidArgumentException;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

final class SelectFactory
{
	public static function action(array $params = []): ActionSelect
	{
		return new ActionSelect($params);
	}

	public static function area(array $params = []): AreaSelect
	{
		return new AreaSelect($params);
	}

	public static function board(array $params = []): BoardSelect
	{
		return new BoardSelect($params);
	}

	public static function category(array $params = []): CategorySelect
	{
		return new CategorySelect(app(CategoryList::class), $params);
	}

	public static function contentClass(array $params = []): ContentClassSelect
	{
		return new ContentClassSelect($params);
	}

	public static function entryType(array $params = []): EntryTypeSelect
	{
		return new EntryTypeSelect($params);
	}

	public static function icon(array $params = []): IconSelect
	{
		return new IconSelect($params);
	}

	public static function page(array $params = []): PageSelect
	{
		return new PageSelect(app(PageList::class), $params);
	}

	public static function pageIcon(array $params = []): PageIconSelect
	{
		return new PageIconSelect($params);
	}

	public static function pageSlug(array $params = []): PageSlugSelect
	{
		return new PageSlugSelect(app(PageList::class), $params);
	}

	public static function permission(array $params = []): PermissionSelect
	{
		return new PermissionSelect($params);
	}

	public static function placement(array $params = []): PlacementSelect
	{
		return new PlacementSelect($params);
	}

	public static function status(array $params = []): StatusSelect
	{
		return new StatusSelect($params);
	}

	public static function tag(array $params = []): TagSelect
	{
		return new TagSelect(app(TagList::class), $params);
	}

	public static function titleClass(array $params = []): TitleClassSelect
	{
		return new TitleClassSelect($params);
	}

	public static function topic(array $params = []): TopicSelect
	{
		return new TopicSelect($params);
	}

	public static function create(string $type, array $params = []): SelectInterface
	{
		return match ($type) {
			'action'        => self::action($params),
			'area'          => self::area($params),
			'board'         => self::board($params),
			'category'      => self::category($params),
			'content_class' => self::contentClass($params),
			'entry_type'    => self::entryType($params),
			'icon'          => self::icon($params),
			'page'          => self::page($params),
			'page_icon'     => self::pageIcon($params),
			'page_slug'     => self::pageSlug($params),
			'permission'    => self::permission($params),
			'placement'     => self::placement($params),
			'status'        => self::status($params),
			'tag'           => self::tag($params),
			'title_class'   => self::titleClass($params),
			'topic'         => self::topic($params),
			default         => throw new InvalidArgumentException("Unknown select type: $type"),
		};
	}
}

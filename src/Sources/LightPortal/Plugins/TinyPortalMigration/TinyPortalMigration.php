<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 18.10.25
 */

namespace LightPortal\Plugins\TinyPortalMigration;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\Icon;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::IMPEX)]
class TinyPortalMigration extends Plugin
{
	private const AREA = 'import_from_tp';

	public function extendAdminAreas(Event $e): void
	{
		$areas = &$e->args->areas;

		if (User::$me->is_admin) {
			$areas['lp_blocks']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];

			$areas['lp_pages']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];

			$areas['lp_categories']['subsections'][self::AREA] = [
				Icon::get('import') . $this->txt['label_name']
			];
		}
	}

	public function extendBlockAreas(Event $e): void
	{
		app()->add(BlockImport::class)
			->addArguments([PortalSqlInterface::class, ErrorHandlerInterface::class]);

		$e->args->areas[self::AREA] = [app(BlockImport::class), 'main'];
	}

	public function extendPageAreas(Event $e): void
	{
		app()->add(PageImport::class)
			->addArguments([PortalSqlInterface::class, ErrorHandlerInterface::class]);

		$e->args->areas[self::AREA] = [app(PageImport::class), 'main'];
	}

	public function extendCategoryAreas(Event $e): void
	{
		app()->add(CategoryImport::class)
			->addArguments([PortalSqlInterface::class, ErrorHandlerInterface::class]);

		$e->args->areas[self::AREA] = [app(CategoryImport::class), 'main'];
	}

	public function onCustomPageImport(Event $e): void
	{
		if ($this->request()->get('sa') !== self::AREA)
			return;

		$items = &$e->args->items;
		$params = &$e->args->params;

		foreach ($items as $pageId => $item) {
			if (in_array('author', $item['options']) || in_array('date', $item['options']))
				$params[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'name'    => 'show_author_and_date',
					'value'   => 1,
				];

			if (in_array('commentallow', $item['options'])) {
				$params[] = [
					'item_id' => $pageId,
					'type'    => 'page',
					'name'    => 'allow_comments',
					'value'   => 1,
				];
			}

			unset($items[$pageId]['options']);
		}

		$e->args->comments = $this->getComments(array_keys($items));
	}

	private function getComments(array $pages): array
	{
		$select = $this->sql->select()
			->from(['com' => 'tp_comments'])
			->join(['mem' => 'members'], 'com.member_id = mem.id_member')
			->where(['item_type' => 'article_comment']);

		if ($pages !== []) {
			$select->where->in('item_id', $pages);
		}

		$result = $this->sql->execute($select);

		$comments = [];
		foreach ($result as $row) {
			if ($row['item_id'] < 0 || empty($row['comment']))
				continue;

			$comments[] = [
				'id'         => $row['id'],
				'parent_id'  => 0,
				'page_id'    => $row['item_id'],
				'author_id'  => $row['member_id'],
				'messages'   => [Config::$language => $row['comment']],
				'created_at' => $row['datetime'],
			];
		}

		return $comments;
	}
}

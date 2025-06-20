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

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Db;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Traits\HasSession;

if (! defined('SMF'))
	die('No direct access...');

final class SessionManager
{
	use HasSession;

	public function __invoke(): array
	{
		return [
			'active_blocks'     => $this->getActiveBlocksCount(),
			'active_pages'      => $this->getActivePagesCount(),
			'my_pages'          => $this->getMyPagesCount(),
			'unapproved_pages'  => $this->getUnapprovedPagesCount(),
			'deleted_pages'     => $this->getDeletedPagesCount(),
			'active_categories' => $this->getActiveCategoriesCount(),
			'active_tags'       => $this->getActiveTagsCount(),
		];
	}

	private function getActiveBlocksCount(): int
	{
		if ($this->session('lp')->get('active_blocks') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(block_id)
				FROM {db_prefix}lp_blocks
				WHERE status = {int:status}',
				[
					'status' => Status::ACTIVE->value,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('active_blocks', (int) $count);
		}

		return $this->session('lp')->get('active_blocks') ?? 0;
	}

	private function getActivePagesCount(): int
	{
		$key = User::$me->allowedTo('light_portal_manage_pages_any') ? '' : ('_u' . User::$me->id);

		if ($this->session('lp')->get('active_pages' . $key) === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}
					AND deleted_at = 0
					AND entry_type = {string:entry_type}' . (User::$me->allowedTo('light_portal_manage_pages_any') ? '' : '
					AND author_id = {int:author}'),
				[
					'status'     => Status::ACTIVE->value,
					'entry_type' => EntryType::DEFAULT->name(),
					'author'     => User::$me->id,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('active_pages' . $key, (int) $count);
		}

		return $this->session('lp')->get('active_pages' . $key) ?? 0;
	}

	private function getMyPagesCount(): int
	{
		$key = User::$me->allowedTo('light_portal_manage_pages_any') ? '' : ('_u' . User::$me->id);

		if ($this->session('lp')->get('my_pages' . $key) === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE author_id = {int:author}
					AND deleted_at = 0
					AND entry_type = {string:entry_type}',
				[
					'author'     => User::$me->id,
					'entry_type' => EntryType::DEFAULT->name(),
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('my_pages' . $key, (int) $count);
		}

		return $this->session('lp')->get('my_pages' . $key) ?? 0;
	}

	private function getUnapprovedPagesCount(): int
	{
		if ($this->session('lp')->get('unapproved_pages') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}
					AND deleted_at = 0',
				[
					'status' => Status::UNAPPROVED->value,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('unapproved_pages', (int) $count);
		}

		return $this->session('lp')->get('unapproved_pages') ?? 0;
	}

	private function getDeletedPagesCount(): int
	{
		if ($this->session('lp')->get('deleted_pages') === null) {
			$result = Db::$db->query('', /** @lang text */ '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE deleted_at <> 0',
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('deleted_pages', (int) $count);
		}

		return $this->session('lp')->get('deleted_pages') ?? 0;
	}

	private function getActiveCategoriesCount(): int
	{
		if ($this->session('lp')->get('active_categories') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(category_id)
				FROM {db_prefix}lp_categories
				WHERE status = {int:status}',
				[
					'status' => Status::ACTIVE->value,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('active_categories', (int) $count);
		}

		return $this->session('lp')->get('active_categories') ?? 0;
	}

	private function getActiveTagsCount(): int
	{
		if ($this->session('lp')->get('active_tags') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(tag_id)
				FROM {db_prefix}lp_tags
				WHERE status = {int:status}',
				[
					'status' => Status::ACTIVE->value,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('active_tags', (int) $count);
		}

		return $this->session('lp')->get('active_tags') ?? 0;
	}
}

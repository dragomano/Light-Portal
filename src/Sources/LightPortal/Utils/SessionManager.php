<?php declare(strict_types=1);

/**
 * SessionManager.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{Db, User, Utils};
use Bugo\LightPortal\Actions\PageInterface;
use Bugo\LightPortal\Actions\PageListInterface;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Helper;

final class SessionManager
{
	use Helper;

	public function __invoke(string $key): int
	{
		return match ($key) {
			'active_blocks'     => $this->getActiveBlocksCount(),
			'active_pages'      => $this->getActivePagesCount(),
			'my_pages'          => $this->getMyPagesCount(),
			'unapproved_pages'  => $this->getUnapprovedPagesCount(),
			'internal_pages'    => $this->getInternalPagesCount(),
			'active_categories' => $this->getActiveCategoriesCount(),
			'active_tags'       => $this->getActiveTagsCount(),
			default             => 0,
		};
	}

	public function getActiveBlocksCount(): int
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

	public function getActivePagesCount(): int
	{
		$key = Utils::$context['allow_light_portal_manage_pages_any'] ? '' : ('_u' . User::$info['id']);

		if ($this->session('lp')->get('active_pages' . $key) === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}' . (Utils::$context['allow_light_portal_manage_pages_any'] ? '' : '
					AND author_id = {int:author}'),
				[
					'status' => Status::ACTIVE->value,
					'author' => User::$info['id'],
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('active_pages' . $key, (int) $count);
		}

		return $this->session('lp')->get('active_pages' . $key) ?? 0;
	}

	public function getMyPagesCount(): int
	{
		$key = Utils::$context['allow_light_portal_manage_pages_any'] ? '' : ('_u' . User::$info['id']);

		if ($this->session('lp')->get('my_pages' . $key) === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE author_id = {int:author}',
				[
					'author' => User::$info['id'],
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('my_pages' . $key, (int) $count);
		}

		return $this->session('lp')->get('my_pages' . $key) ?? 0;
	}

	public function getUnapprovedPagesCount(): int
	{
		if ($this->session('lp')->get('unapproved_pages') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}',
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

	public function getInternalPagesCount(): int
	{
		if ($this->session('lp')->get('internal_pages') === null) {
			$result = Db::$db->query('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}',
				[
					'status' => Status::INTERNAL->value,
				]
			);

			[$count] = Db::$db->fetch_row($result);

			Db::$db->free_result($result);

			$this->session('lp')->put('internal_pages', (int) $count);
		}

		return $this->session('lp')->get('internal_pages') ?? 0;
	}

	public function getActiveCategoriesCount(): int
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

	public function getActiveTagsCount(): int
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

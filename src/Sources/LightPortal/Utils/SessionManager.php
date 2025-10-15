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

use Bugo\Compat\User;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Traits\HasSession;
use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

final readonly class SessionManager
{
	use HasSession;

	public function __construct(private PortalSqlInterface $sql) {}

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
			$select = $this->sql->select('lp_blocks')
				->columns(['count' => new Expression('COUNT(block_id)')])
				->where(['status' => Status::ACTIVE->value]);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('active_blocks', $result['count']);
		}

		return $this->session('lp')->get('active_blocks') ?? 0;
	}

	private function getActivePagesCount(): int
	{
		$key = User::$me->allowedTo('light_portal_manage_pages_any') ? '' : ('_u' . User::$me->id);

		if ($this->session('lp')->get('active_pages' . $key) === null) {
			$select = $this->sql->select('lp_pages')
				->columns(['count' => new Expression('COUNT(page_id)')])
				->where([
					'status'     => Status::ACTIVE->value,
					'deleted_at' => 0,
				]);

			if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
				$select->where(['author_id' => User::$me->id]);
			}

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('active_pages' . $key, $result['count']);
		}

		return $this->session('lp')->get('active_pages' . $key) ?? 0;
	}

	private function getMyPagesCount(): int
	{
		$key = User::$me->allowedTo('light_portal_manage_pages_any') ? '' : ('_u' . User::$me->id);

		if ($this->session('lp')->get('my_pages' . $key) === null) {
			$select = $this->sql->select('lp_pages')
				->columns(['count' => new Expression('COUNT(page_id)')])
				->where([
					'author_id'  => User::$me->id,
					'deleted_at' => 0,
				]);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('my_pages' . $key, $result['count']);
		}

		return $this->session('lp')->get('my_pages' . $key) ?? 0;
	}

	private function getUnapprovedPagesCount(): int
	{
		if ($this->session('lp')->get('unapproved_pages') === null) {
			$select = $this->sql->select('lp_pages')
				->columns(['count' => new Expression('COUNT(page_id)')])
				->where([
					'status'     => Status::UNAPPROVED->value,
					'deleted_at' => 0,
				]);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('unapproved_pages', $result['count']);
		}

		return $this->session('lp')->get('unapproved_pages') ?? 0;
	}

	private function getDeletedPagesCount(): int
	{
		if ($this->session('lp')->get('deleted_pages') === null) {
			$select = $this->sql->select('lp_pages')
				->columns(['count' => new Expression('COUNT(page_id)')]);
			$select->where->notEqualTo('deleted_at', 0);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('deleted_pages', $result['count']);
		}

		return $this->session('lp')->get('deleted_pages') ?? 0;
	}

	private function getActiveCategoriesCount(): int
	{
		if ($this->session('lp')->get('active_categories') === null) {
			$select = $this->sql->select('lp_categories')
				->columns(['count' => new Expression('COUNT(category_id)')])
				->where(['status' => Status::ACTIVE->value]);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('active_categories', (int) $result['count']);
		}

		return $this->session('lp')->get('active_categories') ?? 0;
	}

	private function getActiveTagsCount(): int
	{
		if ($this->session('lp')->get('active_tags') === null) {
			$select = $this->sql->select('lp_tags')
				->columns(['count' => new Expression('COUNT(tag_id)')])
				->where(['status' => Status::ACTIVE->value]);

			$result = $this->sql->execute($select)->current();

			$this->session('lp')->put('active_tags', (int) $result['count']);
		}

		return $this->session('lp')->get('active_tags') ?? 0;
	}
}

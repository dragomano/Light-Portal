<?php declare(strict_types=1);

/**
 * PageList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Repositories\PageRepository;

use function time;

if (! defined('SMF'))
	die('No direct access...');

final readonly class PageList implements ListInterface
{
	public function __construct(private PageRepository $repository) {}

	public function __invoke(): array
	{
		return $this->repository->getAll(
			0,
			$this->repository->getTotalCount(),
			'title',
			'
				AND p.status = {int:status}
				AND entry_type = {string:entry_type}
				AND deleted_at = 0
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			',
			[
				'status'       => Status::ACTIVE->value,
				'entry_type'   => EntryType::DEFAULT->name(),
				'current_time' => time(),
				'permissions'  => Permission::all()
			]
		);
	}
}

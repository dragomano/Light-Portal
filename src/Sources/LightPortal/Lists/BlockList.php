<?php declare(strict_types=1);

/**
 * BlockList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Lists;

use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Traits\HasCache;

if (! defined('SMF'))
	die('No direct access...');

readonly class BlockList implements ListInterface
{
	use HasCache;

	public function __construct(private BlockRepositoryInterface $repository) {}

	public function __invoke(): array
	{
		if (Setting::hideBlocksInACP()) {
			return [];
		}

		return $this->langCache('active_blocks')
			->setFallback(
				fn() => $this->repository->getAll(0, 0, 'placement DESC, priority', 'list')
			);
	}
}

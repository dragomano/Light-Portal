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

namespace LightPortal\Lists;

use LightPortal\Repositories\PageRepositoryInterface;

if (! defined('SMF'))
	die('No direct access...');

readonly class PageList implements ListInterface
{
	public function __construct(private PageRepositoryInterface $repository) {}

	public function __invoke(): array
	{
		return $this->repository->getAll(
			0,
			$this->repository->getTotalCount(),
			'title',
			'list'
		);
	}
}

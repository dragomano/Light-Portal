<?php declare(strict_types=1);

/**
 * TagList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Lists;

use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\Utils\Traits\HasCache;

if (! defined('SMF'))
	die('No direct access...');

readonly class TagList implements ListInterface
{
	use HasCache;

	public function __construct(private TagRepositoryInterface $repository) {}

	public function __invoke(): array
	{
		return $this->langCache('active_tags')
			->setFallback(
				fn() => $this->repository->getAll(0, $this->repository->getTotalCount(), 'title', 'list')
			);
	}
}

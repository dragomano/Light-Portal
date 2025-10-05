<?php declare(strict_types=1);

/**
 * TagList.php
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

use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Repositories\TagRepositoryInterface;

if (! defined('SMF'))
	die('No direct access...');

readonly class TagList implements ListInterface
{
	public function __construct(private TagRepositoryInterface $repository) {}

	public function __invoke(): array
	{
		return $this->repository->getAll(
			0,
			$this->repository->getTotalCount(),
			'title',
			'AND tag.status = {int:status}' . $this->repository->getTranslationFilter(
				'tag', 'tag_id', ['title']
			),
			['status' => Status::ACTIVE->value]
		);
	}
}

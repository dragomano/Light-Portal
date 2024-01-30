<?php declare(strict_types=1);

/**
 * PageList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Actions\PageInterface;
use Bugo\LightPortal\Repositories\PageRepository;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class PageList implements ListInterface
{
	private PageRepository $repository;

	public function __construct()
	{
		$this->repository = new PageRepository();
	}

	/**
	 * @throws IntlException
	 */
	public function getAll(): array
	{
		return $this->repository->getAll(0, $this->repository->getTotalCount(), 'p.page_id DESC', 'AND p.status = {int:status}', ['status' => PageInterface::STATUS_ACTIVE]);
	}
}

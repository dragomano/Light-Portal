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

namespace LightPortal\Actions;

use LightPortal\Articles\Services\ArticleServiceInterface;
use LightPortal\Utils\Traits\HasBreadcrumbs;
use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPageList implements PageListInterface
{
	use HasBreadcrumbs;
	use HasRequest;

	public function __construct(
		protected CardListInterface $cardList,
		protected ArticleServiceInterface $articleService
	)
	{
		$this->articleService->init();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		return iterator_to_array($this->articleService->getData($start, $limit, $sort));
	}

	public function getTotalPages(): int
	{
		return $this->articleService->getTotalCount();
	}
}

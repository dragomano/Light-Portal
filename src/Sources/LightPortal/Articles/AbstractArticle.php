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

namespace LightPortal\Articles;

use LightPortal\Articles\Services\ArticleServiceInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractArticle implements ArticleInterface
{
	public function __construct(protected ArticleServiceInterface $service) {}

	public function init(): void
	{
		$this->service->init();
	}

	public function getSortingOptions(): array
	{
		return $this->service->getSortingOptions();
	}

	public function getData(int $start, int $limit, ?string $sortType): iterable
	{
		return $this->service->getData($start, $limit, $sortType);
	}

	public function getTotalCount(): int
	{
		return $this->service->getTotalCount();
	}
}

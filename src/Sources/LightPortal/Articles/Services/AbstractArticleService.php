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

namespace LightPortal\Articles\Services;

use LightPortal\Articles\Queries\ArticleQueryInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;

abstract class AbstractArticleService implements ArticleServiceInterface
{
	public function __construct(
		protected ArticleQueryInterface $query,
		protected EventDispatcherInterface $dispatcher
	) {}

	abstract public function getParams(): array;

	final public function init(): void
	{
		$this->query->init($this->getParams());
	}

	final public function getData(int $start, int $limit, ?string $sortType): iterable
	{
		$this->query->setSorting($sortType);
		$this->query->prepareParams($start, $limit);

		foreach ($this->query->getRawData() as $row) {
			$item = array_map(fn($callback) => $callback($row), $this->getRules($row));

			$articles = [$item['id'] => $item];

			$this->dispatcher->dispatch($this->getEventHook(), ['articles' => &$articles, 'row' => $row]);

			$item = $articles[$item['id']];

			yield $item['id'] => $this->finalizeItem($item);
		}
	}

	final public function getTotalCount(): int
	{
		return $this->query->getTotalCount();
	}

	abstract protected function getRules(array $row): array;

	abstract protected function getEventHook(): PortalHook;

	abstract protected function finalizeItem(array $item): array;
}

<?php declare(strict_types=1);

namespace Bugo\LightPortal\UI\Breadcrumbs;

use Bugo\Bricks\Breadcrumbs\BreadcrumbItem;
use Bugo\Bricks\Breadcrumbs\Interfaces\BreadcrumbBuilderInterface as Builder;
use Bugo\Bricks\Breadcrumbs\Interfaces\BreadcrumbPresenterInterface as Presenter;
use Bugo\Compat\Utils;

use function in_array;

/**
 * @method self add(string $name, ?string $url = null, ?string $before = null, ?string $after = null)
 * @method self addItem(BreadcrumbItem $item)
 * @method self addItems(array $items)
 * @method self update(int $index, string $key, mixed $value)
 * @method self remove(int $index)
 * @method array getAll()
 * @method array|null getByIndex(int $index)
 */
final readonly class BreadcrumbWrapper
{
	private const MUTATORS = ['add', 'addItem', 'addItems', 'update', 'remove'];

	public function __construct(private Builder $builder, private Presenter $presenter)
	{
		Utils::$context['linktree'] ??= [];

		foreach (Utils::$context['linktree'] as $item) {
			$this->builder->add(
				$item['name'] ?? '',
				$item['url'] ?? null,
				$item['extra_before'] ?? null,
				$item['extra_after'] ?? null,
			);
		}
	}

	public function __call(string $method, array $arguments): mixed
	{
		$result = $this->builder->{$method}(...$arguments);

		if (in_array($method, self::MUTATORS, true)) {
			$this->presenter->show($this->builder);
		}

		return $result instanceof Builder ? $this : $result;
	}
}

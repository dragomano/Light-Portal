<?php declare(strict_types=1);

namespace Bugo\LightPortal\UI\Breadcrumbs;

use Bugo\Bricks\RendererInterface;
use Bugo\Compat\Utils;

final class BreadcrumbRenderer implements RendererInterface
{
	public function render(array $data): void
	{
		Utils::$context['linktree'] = [];

		foreach ($data as $item) {
			Utils::$context['linktree'][] = array_filter([
				'name'         => $item['name'],
				'url'          => $item['url'] ?? null,
				'extra_before' => $item['before'] ?? null,
				'extra_after'  => $item['after'] ?? null,
			]);
		}
	}
}

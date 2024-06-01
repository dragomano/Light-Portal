<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Filters;

use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Psr\Container\ContainerInterface;

final class SnakeNameFilterFactory
{
	public function __invoke(ContainerInterface $container): SnakeNameFilter
	{
		$filterChain = new FilterChain();
		$filterChain->attach(new CamelCaseToUnderscore(), 1001);
		$filterChain->attach(new StringToLower());
		$filter = new SnakeNameFilter();
		$filter->setFilterChain($filterChain);
		return $filter;
	}
}

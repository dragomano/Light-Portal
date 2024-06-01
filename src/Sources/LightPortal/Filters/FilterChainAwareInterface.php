<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Filters;

use Laminas\Filter\FilterChain;

interface FilterChainAwareInterface
{
	public function setFilterChain(FilterChain $filterChain): void;
	public function getFilterChain(): FilterChain;
}

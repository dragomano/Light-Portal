<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Filters;

use Laminas\Filter\FilterChain;

trait FilterChainAwareTrait
{
	protected FilterChain $filterChain;

	public function setFilterChain(FilterChain $filterChain): void
	{
		$this->filterChain = $filterChain;
	}

	public function getFilterChain(): FilterChain
	{
		return $this->filterChain;
	}
}

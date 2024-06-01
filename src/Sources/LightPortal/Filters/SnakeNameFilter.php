<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Filters;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\FilterChain;

class SnakeNameFilter extends AbstractFilter implements FilterChainAwareInterface
{
	use FilterChainAwareTrait;

	/**
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter($value)
	{
		$filter = $this->getFilterChain();
		return $filter->filter($value);
	}

}

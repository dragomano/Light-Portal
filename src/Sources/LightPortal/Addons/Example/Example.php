<?php

namespace Bugo\LightPortal\Addons\Example;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class Example extends Block
{
	public string $icon = 'fas fa-cat';

	public function exampleMethod(array &$params)
	{
		$params[] = 1;
	}
}

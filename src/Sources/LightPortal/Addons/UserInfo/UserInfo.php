<?php

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class UserInfo extends Block
{
	public string $icon = 'fas fa-user';

	public function init()
	{
		var_dump('Hello from init method');
	}
}

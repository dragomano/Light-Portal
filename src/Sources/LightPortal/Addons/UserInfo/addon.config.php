<?php

declare(strict_types=1);

use Bugo\LightPortal\Addons\UserInfo\UserInfo;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
	'installed_addons' => [
		UserInfo::class => __DIR__,
	],
	'lp_addons' => [
		'aliases'   => [
			'user_info' => UserInfo::class,
		],
		'factories' => [
			UserInfo::class => InvokableFactory::class
		],
 	],
];

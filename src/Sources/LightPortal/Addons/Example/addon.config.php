<?php

declare(strict_types=1);

use Bugo\LightPortal\Addons\Example\Example;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
	'installed_addons' => [
		Example::class => __DIR__,
	],
	'lp_addons' => [
		'aliases'   => [
			'example' => Example::class
		],
		'factories' => [
			Example::class => InvokableFactory::class,
		],
	],
];

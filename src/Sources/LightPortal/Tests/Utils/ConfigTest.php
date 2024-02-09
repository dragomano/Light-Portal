<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Config;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check properties', function () {
	Assert::type('array', Config::$modSettings);
	Assert::type('string', Config::$scripturl);
	Assert::type('string', Config::$boardurl);
	Assert::type('string', Config::$boarddir);
	Assert::type('string', Config::$sourcedir);
	Assert::type('string', Config::$cachedir);
	Assert::type('string', Config::$db_type);
	Assert::type('string', Config::$db_prefix);
	Assert::type('string', Config::$language);
	Assert::type('int', Config::$cache_enable);
	Assert::type('bool', Config::$db_show_debug);
});

test('check methods', function () {
	Assert::true(method_exists(Config::class, 'updateModSettings'));
});

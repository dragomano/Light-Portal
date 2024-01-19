<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Utils;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check properties', function () {
	Assert::type('array', Utils::$context);
	Assert::type('array', Utils::$smcFunc);
});

test('check methods', function () {
	Assert::true(method_exists(Utils::class, 'JavaScriptEscape'));
	Assert::true(method_exists(Utils::class, 'obExit'));
	Assert::true(method_exists(Utils::class, 'redirectexit'));
	Assert::true(method_exists(Utils::class, 'sendHttpStatus'));
	Assert::true(method_exists(Utils::class, 'shorten'));
	Assert::true(method_exists(Utils::class, 'makeWritable'));
	Assert::true(method_exists(Utils::class, 'jsonDecode'));
	Assert::true(method_exists(Utils::class, 'htmlspecialcharsDecode'));
});

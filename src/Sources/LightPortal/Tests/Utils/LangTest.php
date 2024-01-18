<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Lang;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check properties', function () {
	Assert::type('array', Lang::$txt);
	Assert::type('array', Lang::$editortxt);
});

test('check methods', function () {
	Assert::true(method_exists(Lang::class, 'censorText'));
	Assert::true(method_exists(Lang::class, 'get'));
	Assert::true(method_exists(Lang::class, 'load'));
	Assert::true(method_exists(Lang::class, 'sentenceList'));
});

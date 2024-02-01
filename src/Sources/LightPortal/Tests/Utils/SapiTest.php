<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Sapi;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check methods', function () {
	Assert::true(method_exists(Sapi::class, 'memoryReturnBytes'));
	Assert::true(method_exists(Sapi::class, 'getTempDir'));
	Assert::true(method_exists(Sapi::class, 'setTimeLimit'));
});

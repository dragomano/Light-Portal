<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\ErrorHandler;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check methods', function () {
	Assert::true(method_exists(ErrorHandler::class, 'fatal'));
	Assert::true(method_exists(ErrorHandler::class, 'fatalLang'));
	Assert::true(method_exists(ErrorHandler::class, 'log'));
});

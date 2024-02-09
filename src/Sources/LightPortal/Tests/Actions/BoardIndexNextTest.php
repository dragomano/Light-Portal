<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\BoardIndexNext;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has show method', function () {
	Assert::true(method_exists(BoardIndexNext::class, 'show'));
});

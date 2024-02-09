<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\Category;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has methods', function () {
	Assert::true(method_exists(Category::class, 'show'));
	Assert::true(method_exists(Category::class, 'getPages'));
	Assert::true(method_exists(Category::class, 'getTotalCount'));
	Assert::true(method_exists(Category::class, 'showAll'));
	Assert::true(method_exists(Category::class, 'getAll'));
});

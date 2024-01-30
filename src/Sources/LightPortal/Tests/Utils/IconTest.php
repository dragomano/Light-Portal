<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Icon;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check methods', function () {
	Assert::true(method_exists(Icon::class, 'get'));
	Assert::true(method_exists(Icon::class, 'all'));
});

test('get method', function () {
	Assert::same('<i class="fa-solid fa-user" aria-hidden="true"></i> ', Icon::get('user'));
});

test('get method with title', function () {
	Assert::same('<i title="bar" class="fa-solid fa-user" aria-hidden="true"></i> ', Icon::get('user', 'bar'));
});

test('all method', function () {
	Assert::type('array', Icon::all());
});

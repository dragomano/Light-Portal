<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\Tag;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has methods', function () {
	Assert::true(method_exists(Tag::class, 'show'));
	Assert::true(method_exists(Tag::class, 'getPages'));
	Assert::true(method_exists(Tag::class, 'getTotalCount'));
	Assert::true(method_exists(Tag::class, 'showAll'));
	Assert::true(method_exists(Tag::class, 'getAll'));
});

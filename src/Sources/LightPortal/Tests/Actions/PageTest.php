<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\Page;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has methods', function () {
	Assert::true(method_exists(Page::class, 'show'));
	Assert::true(method_exists(Page::class, 'getData'));
	Assert::true(method_exists(Page::class, 'getDataByAlias'));
	Assert::true(method_exists(Page::class, 'getDataByItem'));
	Assert::true(method_exists(Page::class, 'showAsCards'));
	Assert::true(method_exists(Page::class, 'getList'));
});

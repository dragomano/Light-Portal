<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\LightPortal\Actions\FrontPage;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('has methods', function () {
	Assert::true(method_exists(FrontPage::class, 'show'));
	Assert::true(method_exists(FrontPage::class, 'prepare'));
	Assert::true(method_exists(FrontPage::class, 'prepareTemplates'));
	Assert::true(method_exists(FrontPage::class, 'prepareLayoutSwitcher'));
	Assert::true(method_exists(FrontPage::class, 'getLayouts'));
	Assert::true(method_exists(FrontPage::class, 'view'));
	Assert::true(method_exists(FrontPage::class, 'getNumColumns'));
	Assert::true(method_exists(FrontPage::class, 'getOrderBy'));
	Assert::true(method_exists(FrontPage::class, 'updateStart'));
});

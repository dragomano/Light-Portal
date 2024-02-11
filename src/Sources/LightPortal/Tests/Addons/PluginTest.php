<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Addons;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Addons\Plugin;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('public properties', function () {
	$testPlugin = new class extends Plugin {};

	Assert::same('block', $testPlugin->type);
	Assert::same('fas fa-puzzle-piece', $testPlugin->icon);
});

test('getName method', function () {
	$testPlugin = new DummyPlugin();

	Assert::same('DummyPlugin', $testPlugin->getName());
});

test('getFromSsi method', function () {
	$testPlugin = new class extends Plugin {};

	// ssi_unknownFunction does not exist
	Assert::false($testPlugin->getFromSsi('unknownFunction'));

	// ssi_welcome does exist
	Assert::same(Utils::$context['user'], $testPlugin->getFromSsi('welcome', 'array'));
});

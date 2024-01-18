<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Theme;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('$current is object', function () {
	Assert::type('object', Theme::$current);
});

test('check properties', function () {
	Assert::type('array', Theme::$current->settings);
	Assert::type('array', Theme::$current->options);
});

test('check methods', function () {
	Assert::true(method_exists(Theme::class, 'addInlineJS'));
	Assert::true(method_exists(Theme::class, 'loadExtCSS'));
	Assert::true(method_exists(Theme::class, 'loadExtJS'));
	Assert::true(method_exists(Theme::class, 'loadJSFile'));
	Assert::true(method_exists(Theme::class, 'addJavaScriptVar'));
	Assert::true(method_exists(Theme::class, 'addInlineCss'));
	Assert::true(method_exists(Theme::class, 'addInlineJavaScript'));
	Assert::true(method_exists(Theme::class, 'loadCSSFile'));
	Assert::true(method_exists(Theme::class, 'loadJavaScriptFile'));
	Assert::true(method_exists(Theme::class, 'loadEssential'));
	Assert::true(method_exists(Theme::class, 'loadTemplate'));
});

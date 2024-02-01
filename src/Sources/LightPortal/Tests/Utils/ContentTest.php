<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\Content;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('prepare helper', function () {
	Assert::same('', Content::prepare());
});

test('parse helper with BBCode', function () {
	Assert::same('<b>Hello</b>', Content::parse('[b]Hello[/b]'));
});

test('parse helper with HTML', function () {
	$content = '<div class="example">&nbsp;</div>';

	Assert::same(str_replace('&nbsp;',  ' ', $content), Content::parse($content, 'html'));
});

test('parse helper with PHP', function () {
	Assert::same('123', Content::parse('echo 123;', 'php'));
});

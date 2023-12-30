<?php declare(strict_types=1);

/**
 * @phpExtension intl
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\LightPortal\Helper;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

function __(string $pattern, array $values = []): string
{
	$class = new class {
		use Helper;
	};

	return $class->translate($pattern, $values);
}

test('core portal helpers exist', function () {
	Assert::true(function_exists('call_portal_hook'));
	Assert::true(function_exists('prepare_content'));
	Assert::true(function_exists('parse_content'));
});

test('prepare_content helper', function () {
	Assert::same('', prepare_content());
});

test('parse_content helper with BBCode', function () {
	Assert::same('<b>Hello</b>', parse_content('[b]Hello[/b]'));
});

test('parse_content helper with HTML', function () {
	$content = '<div class="example">&nbsp;</div>';

	Assert::same(str_replace('&nbsp;',  ' ', $content), parse_content($content, 'html'));
});

test('parse_content helper with PHP', function () {
	Assert::same('123', parse_content('echo 123;', 'php'));
});

test('translate helper with empty pattern', function () {
	Assert::same('', __(''));
});

test('translate helper without proper pattern', function () {
	Assert::same('test', __('test'));
});

test('translate helper with English locale', function () {
	loadLanguage('LightPortal/LightPortal', 'english');

	Assert::same('a day', __('lp_days_set', ['days' => 1]));
	Assert::same('3 days', __('lp_days_set', ['days' => 3]));
	Assert::same('10 days', __('lp_days_set', ['days' => 10]));
});

test('translate helper with Russian locale', function () {
	loadLanguage('LightPortal/LightPortal', 'russian');

	Assert::same('день', __('lp_days_set', ['days' => 1]));
	Assert::same('3 дня', __('lp_days_set', ['days' => 3]));
	Assert::same('10 дней', __('lp_days_set', ['days' => 10]));
});

test('translate helper without parameters', function () {
	Assert::same('{days}', __('lp_days_set'));
});

test('translate helper with gender custom field', function () {
	loadLanguage('LightPortal/LightPortal', 'english');

	Assert::same('{member_link} left a comment {content_subject}', __('alert_new_comment_page_comment', ['gender' => 'male']));
	Assert::same('{member_link} left a comment {content_subject}', __('alert_new_comment_page_comment', ['gender' => 'female']));

	loadLanguage('LightPortal/LightPortal', 'russian');

	Assert::same('{member_link} оставил комментарий {content_subject}', __('alert_new_comment_page_comment', ['gender' => 'male']));
	Assert::same('{member_link} оставила комментарий {content_subject}', __('alert_new_comment_page_comment', ['gender' => 'female']));
});

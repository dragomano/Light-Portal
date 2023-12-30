<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('checkbox field', function () {
	global $context;

	CheckboxField::make('foo', 'bar')
		->setValue(true);

	Assert::same(
		'bar',
		$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'foo',
		$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::same(
		'checkbox',
		$context['posting_fields']['foo']['input']['attributes']['class']
	);

	Assert::true($context['posting_fields']['foo']['input']['attributes']['checked']);
});

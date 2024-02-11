<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('checkbox field', function () {
	CheckboxField::make('foo', 'bar')
		->setValue(true);

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'foo',
		Utils::$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::same(
		'checkbox',
		Utils::$context['posting_fields']['foo']['input']['attributes']['class']
	);

	Assert::true(Utils::$context['posting_fields']['foo']['input']['attributes']['checked']);
});

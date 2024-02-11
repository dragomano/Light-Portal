<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('number field', function () {
	NumberField::make('foo', 'bar')
		->setValue(1);

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'number',
		Utils::$context['posting_fields']['foo']['input']['type']
	);

	Assert::same(
		'foo',
		Utils::$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::same(
		1,
		Utils::$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

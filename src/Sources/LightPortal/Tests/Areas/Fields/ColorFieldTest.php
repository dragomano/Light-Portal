<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\ColorField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('color field', function () {
	global $context;

	ColorField::make('foo', 'bar')
		->setValue('#ddd');

	Assert::same(
		'bar',
		$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'color',
		$context['posting_fields']['foo']['input']['type']
	);

	Assert::same(
		'#ddd',
		$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

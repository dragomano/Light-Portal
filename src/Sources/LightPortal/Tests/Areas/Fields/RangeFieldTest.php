<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\RangeField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('range field', function () {
	global $context;

	RangeField::make('foo', 'bar')
		->setAttribute('min', 1)
		->setAttribute('max', 3)
		->setTab('other')
		->setValue(2);

	Assert::same(
		'bar',
		$context['posting_fields']['foo']['label']['html']
	);

	Assert::same(
		'other',
		$context['posting_fields']['foo']['input']['tab']
	);

	Assert::notNull($context['posting_fields']['foo']['input']['html']);
});
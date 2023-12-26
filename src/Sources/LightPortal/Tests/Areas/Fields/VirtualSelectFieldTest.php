<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\VirtualSelectField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('virtual select field', function () {
	global $context;

	$context['javascript_inline']['defer'] = [];

	VirtualSelectField::make('foo', 'bar')
		->setOptions([
			1 => 'option 1',
			2 => 'option 2',
			3 => 'option 3',
		])
		->setValue(2);

	Assert::false(empty($context['javascript_inline']['defer']));

	Assert::same(
		'bar',
		$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'foo',
		$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::same(
		'select',
		$context['posting_fields']['foo']['input']['type']
	);

	Assert::true(is_array($context['posting_fields']['foo']['input']['options']));

	Assert::true($context['posting_fields']['foo']['input']['options']['option 2']['selected']);

	Assert::false($context['posting_fields']['foo']['input']['options']['option 1']['selected']);

	Assert::same(
		2,
		$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

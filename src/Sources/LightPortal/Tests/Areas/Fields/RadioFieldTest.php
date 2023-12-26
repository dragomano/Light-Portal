<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\RadioField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('radio field', function () {
	global $context;

	RadioField::make('foo', 'bar')
		->setOptions([
			1 => 'Yes',
			2 => 'No'
		])
		->setValue(2);

	Assert::same(
		'bar',
		$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'radio_select',
		$context['posting_fields']['foo']['input']['type']
	);

	Assert::same(
		'foo',
		$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::true(is_array($context['posting_fields']['foo']['input']['options']));

	Assert::true($context['posting_fields']['foo']['input']['options']['No']['selected']);

	Assert::false($context['posting_fields']['foo']['input']['options']['Yes']['selected']);

	Assert::same(
		2,
		$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

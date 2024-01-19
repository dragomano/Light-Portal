<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\VirtualSelectField;
use Bugo\LightPortal\Utils\Utils;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('virtual select field', function () {
	Utils::$context['javascript_inline']['defer'] = [];

	VirtualSelectField::make('foo', 'bar')
		->setOptions([
			1 => 'option 1',
			2 => 'option 2',
			3 => 'option 3',
		])
		->setValue(2);

	Assert::false(empty(Utils::$context['javascript_inline']['defer']));

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'foo',
		Utils::$context['posting_fields']['foo']['input']['attributes']['id']
	);

	Assert::same(
		'select',
		Utils::$context['posting_fields']['foo']['input']['type']
	);

	Assert::true(is_array(Utils::$context['posting_fields']['foo']['input']['options']));

	Assert::true(Utils::$context['posting_fields']['foo']['input']['options']['option 2']['selected']);

	Assert::false(Utils::$context['posting_fields']['foo']['input']['options']['option 1']['selected']);

	Assert::same(
		2,
		Utils::$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

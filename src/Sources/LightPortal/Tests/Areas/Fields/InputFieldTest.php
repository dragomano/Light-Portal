<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\InputField;
use Bugo\LightPortal\Utils\Utils;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('input field', function () {
	InputField::make('foo', 'bar')
		->setType('type')
		->setAfter('after')
		->setValue('test');

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'type',
		Utils::$context['posting_fields']['foo']['input']['type']
	);

	Assert::same(
		'tuning',
		Utils::$context['posting_fields']['foo']['input']['tab']
	);

	Assert::same(
		'after',
		Utils::$context['posting_fields']['foo']['input']['after']
	);

	Assert::same(
		'test',
		Utils::$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

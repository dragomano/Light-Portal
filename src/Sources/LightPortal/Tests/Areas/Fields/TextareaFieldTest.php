<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\LightPortal\Areas\Fields\TextareaField;
use Bugo\LightPortal\Utils\Utils;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('textarea field', function () {
	TextareaField::make('foo', 'bar')
		->setValue('lorem ipsum');

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['text'],
	);

	Assert::same(
		'textarea',
		Utils::$context['posting_fields']['foo']['input']['type']
	);

	Assert::same(
		'lorem ipsum',
		Utils::$context['posting_fields']['foo']['input']['attributes']['value']
	);
});

<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Areas\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Tester\Assert;

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once __DIR__ . '/template.php';

test('custom field', function () {
	CustomField::make('foo', 'bar')
		->setValue([1, 2, 3]);

	Assert::same(
		'bar',
		Utils::$context['posting_fields']['foo']['label']['html'],
	);

	Assert::same(
		[1, 2, 3],
		Utils::$context['posting_fields']['foo']['input']['html']
	);
});

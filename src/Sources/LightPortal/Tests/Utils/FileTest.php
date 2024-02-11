<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Helper;
use Tester\Assert;
use Tester\FileMock;

require_once dirname(__DIR__) . '/bootstrap.php';

test('free method', function () {
	$class = new class {
		use Helper;
	};

	$_FILES['temp'] = FileMock::create();

	Assert::same($class->files('temp'), $_FILES['temp']);

	$class->files()->free('temp');

	Assert::same([], $_FILES);
});

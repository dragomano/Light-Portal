<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\Utils;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

test('prepareForumLanguages helper', function () {
	$class = new class {
		use Helper;
	};

	unset(Utils::$context['languages']);

	$class->prepareForumlanguages();

	Assert::hasKey('english', Utils::$context['lp_languages']);
});

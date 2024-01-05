<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\LightPortal\Helper;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

test('prepareForumLanguages helper', function () {
	global $context;

	$class = new class {
		use Helper;
	};

	unset($context['languages']);

	$class->prepareForumlanguages();

	Assert::hasKey('english', $context['lp_languages']);
});

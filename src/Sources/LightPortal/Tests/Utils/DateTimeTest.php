<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Utils\DateTime;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('relative method', function () {
	Assert::same(Lang::$txt['lp_just_now'], DateTime::relative(time()));
});

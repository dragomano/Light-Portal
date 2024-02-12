<?php declare(strict_types=1);

namespace Tests\Actions;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\Block;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('show method', function () {
	Assert::contains('lp_portal', Utils::$context['template_layers']);
});

test('getActive method', function () {
	Assert::type('array', (new Block())->getActive());
});
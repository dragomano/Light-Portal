<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\PluginStorage;
use stdClass;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

test('getHash method', function () {
	$object = new stdClass();
	Assert::same($object::class, (new PluginStorage)->getHash($object));
});

test('getAll method', function () {
	Assert::type('array', AddonHandler::getInstance()->getAll());
});

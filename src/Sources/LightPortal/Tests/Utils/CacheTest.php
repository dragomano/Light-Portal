<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\Compat\Config;
use Bugo\LightPortal\Utils\Cache;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

setUp(function () {
	$cache = new Cache();
	$cache->flush();

	Config::$cache_enable = 0;
});

test('get method', function () {
	$cache = new Cache('test_key', 3600);

	Assert::null($cache->get('test_key'));

	$cache->put('test_key', ['value' => 'test_value']);

	Assert::same(empty(Config::$cache_enable) ? null : ['value' => 'test_value'], $cache->get('test_key'));
});

test('put method', function () {
	$cache = new Cache('test_key', 3600);

	$cache->put('test_key', ['value' => 'test_value']);

	Assert::same(empty(Config::$cache_enable) ? null : ['value' => 'test_value'], $cache->get('test_key'));
});

test('forget method', function () {
	$cache = new Cache('test_key', 3600);

	$cache->put('test_key', ['value' => 'test_value']);
	$cache->put('test_other_key', ['value' => 'test_value']);

	Assert::same(empty(Config::$cache_enable) ? null : ['value' => 'test_value'], $cache->get('test_key'));

	$cache->forget('test_key');

	Assert::null($cache->get('test_key'));
	Assert::same(empty(Config::$cache_enable) ? null : ['value' => 'test_value'], $cache->get('test_other_key'));
});

test('flush method', function () {
	$cache = new Cache('test_key', 3600);

	$cache->put('test_key', ['value' => 'test_value']);

	Assert::same(empty(Config::$cache_enable) ? null : ['value' => 'test_value'], $cache->get('test_key'));

	$cache->flush();

	Assert::null($cache->get('test_key'));
});

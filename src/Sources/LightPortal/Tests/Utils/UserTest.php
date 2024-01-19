<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Utils\User;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

test('check properties', function () {
	Assert::type('array', User::$info);
	Assert::type('array', User::$profiles);
	Assert::type('array', User::$settings);
	Assert::type('array', User::$memberContext);
});

test('$me is object', function () {
	Assert::type('object', User::$me);
	Assert::same(new User(), User::$me);
});

test('check methods', function () {
	Assert::true(method_exists(User::class, 'hasPermission'));
	Assert::true(method_exists(User::class, 'checkSession'));
	Assert::true(method_exists(User::class, 'mustHavePermission'));
	Assert::true(method_exists(User::class, 'loadMemberData'));
	Assert::true(method_exists(User::class, 'loadMemberContext'));
	Assert::true(method_exists(User::class, 'membersAllowedTo'));
	Assert::true(method_exists(User::class, 'updateMemberData'));
});

test('hasPermission method', function () {
	Assert::type('bool', User::hasPermission('light_portal_view'));
});

test('loadMemberData method', function () {
	Assert::type('array', User::loadMemberData([1]));
});

test( 'loadMemberContext method', function () {
	Assert::type('array', User::loadMemberContext(1));
});

test( 'membersAllowedTo method', function () {
	Assert::type('array', User::membersAllowedTo('light_portal_view'));
});
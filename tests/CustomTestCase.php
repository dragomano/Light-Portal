<?php

declare(strict_types=1);

namespace Tests;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;

class CustomTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Config::$sourcedir = __DIR__ . '/files';
        Config::$scripturl = 'https://example.com';

        Utils::$context = &$GLOBALS['context'];
        Utils::$smcFunc = &$GLOBALS['smcFunc'];
        Utils::$context['admin_menu_name'] = 'admin';

        User::$me = new User(1);
        User::$me->name = 'TestUser';
        User::$me->groups = [1];

        array_map(fn($u) => new $u(), [
            Config::class,
            Lang::class,
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        Mockery::close();
    }
}

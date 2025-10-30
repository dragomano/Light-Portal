<?php

declare(strict_types=1);

namespace Tests;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;

class CustomTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (isset($GLOBALS['_INITIAL_STATE'])) {
            foreach ($GLOBALS['_INITIAL_STATE'] as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }

        array_map(fn($u) => new $u(), [
            Config::class,
            Lang::class,
            Theme::class,
            Utils::class,
        ]);

        Utils::$smcFunc['ucfirst'] = 'ucfirst';
        Utils::$smcFunc['strtolower'] = 'strtolower';
        Utils::$smcFunc['htmlspecialchars'] = 'htmlspecialchars';

        User::$me = new User(1);
        User::$me->name = 'TestUser';
        User::$me->language = 'english';
        User::$me->groups = [0];
        User::$me->is_guest = false;
        User::$me->is_admin = false;
        User::$me->permissions = ['manage_boards'];
        User::$me->allowedTo = fn($permission) => false;
    }

    protected function tearDown(): void
    {
        AppMockRegistry::clear();

        Mockery::close();
    }
}

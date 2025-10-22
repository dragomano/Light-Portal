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
use stdClass;

class CustomTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Config::$sourcedir = __DIR__ . '/files';
        Config::$scripturl = 'https://example.com';
        Config::$modSettings = ['avatar_url' => '', 'smileys_url' => 'https://example.com/Smileys'];

        Utils::$context = &$GLOBALS['context'];
        Utils::$smcFunc = &$GLOBALS['smcFunc'];

        Utils::$smcFunc['strtolower'] = 'strtolower';
        Utils::$smcFunc['htmlspecialchars'] = 'htmlspecialchars';

        Utils::$context['admin_menu_name'] = 'admin';
        Utils::$context['right_to_left'] = false;

        $themeCurrent = new stdClass();
        $themeCurrent->settings = ['default_theme_dir' => '/themes/default'];
        Theme::$current = $themeCurrent;

        User::$me = new User(1);
        User::$me->name = 'TestUser';
        User::$me->groups = [0];
        User::$me->is_guest = false;
        User::$me->is_admin = false;
        User::$me->allowedTo = fn($permission) => false;

        array_map(fn($u) => new $u(), [
            Config::class,
            Lang::class,
        ]);

        Lang::$txt['guest_title'] = 'Guest';
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        Mockery::close();
    }
}

<?php

declare(strict_types=1);

use Bugo\Compat\User;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\Notifier;
use LightPortal\Utils\NotifierInterface;
use Tests\ReflectionAccessor;

arch()
    ->expect(Notifier::class)
    ->toImplement(NotifierInterface::class);

describe('Notifier', function () {
    beforeEach(function () {
        $GLOBALS['user_info']['id'] = 1;
        $GLOBALS['user_info']['name'] = 'TestUser';
        $GLOBALS['user_profile'] = [1 => []];

        User::$me = new User();
        User::$profiles = &$GLOBALS['user_profile'];
    });

    afterEach(function () {
        User::$profiles = [];
        unset($GLOBALS['user_info'], $GLOBALS['user_profile']);
    });

    describe('__construct()', function () {
        it('initializes with PortalSqlInterface', function () {
            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            expect($notifier)->toBeInstanceOf(Notifier::class);
        });
    });

    describe('notify()', function () {
        it('does nothing when options are empty', function () {
            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            $notifier->notify('page', 'create');

            $mockSql->shouldNotHaveReceived('insert');
        });
    });

    describe('getUserGender()', function () {
        it('returns male when user profile not found', function () {
            User::$profiles = [];

            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            $reflection = new ReflectionAccessor($notifier);
            $result = $reflection->callMethod('getUserGender');
            expect($result)->toBe('male');
        });

        it('returns male when gender option is not set', function () {
            User::$profiles = [1 => ['options' => []]];

            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            $reflection = new ReflectionAccessor($notifier);
            $result = $reflection->callMethod('getUserGender');
            expect($result)->toBe('male');
        });

        it('returns female when gender is {gender_2}', function () {
            User::$profiles = [1 => ['options' => ['cust_gender' => '{gender_2}']]];

            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            $reflection = new ReflectionAccessor($notifier);
            $result = $reflection->callMethod('getUserGender');
            expect($result)->toBe('female');
        });

        it('returns male when gender is {gender_1}', function () {
            User::$profiles = [1 => ['options' => ['cust_gender' => '{gender_1}']]];

            $mockSql = mock(PortalSqlInterface::class);
            $notifier = new Notifier($mockSql);

            $reflection = new ReflectionAccessor($notifier);
            $result = $reflection->callMethod('getUserGender');
            expect($result)->toBe('male');
        });
    });
});

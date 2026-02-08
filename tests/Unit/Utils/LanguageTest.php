<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Utils\Language;

arch()
    ->expect(Language::class)
    ->toHaveMethods(['getFallbackValue', 'getNameFromLocale', 'getCurrent', 'isDefault', 'prepareList']);

describe('Language', function () {
    describe('FALLBACK', function () {
        it('has correct fallback value', function () {
            expect(Language::FALLBACK)->toBe('english');
        });
    });

    describe('getFallbackValue()', function () {
        it('returns string value', function () {
            $result = Language::getFallbackValue();
            expect($result)->toBeString();
        });
    });

    describe('getNameFromLocale()', function () {
        it('returns string value', function () {
            $result = Language::getNameFromLocale('russian');
            expect($result)->toBeString();
        });
    });

    describe('getCurrent()', function () {
        it('returns config language when userLanguage is empty', function () {
            Config::$modSettings['userLanguage'] = '';
            Config::$language = 'english';

            $result = Language::getCurrent();
            expect($result)->toBe('english');
        });

        it('returns user language when userLanguage is set', function () {
            Config::$modSettings['userLanguage'] = '1';

            User::$me->language = 'russian';

            $result = Language::getCurrent();
            expect($result)->toBe('russian');
        });
    });

    describe('isDefault()', function () {
        it('returns true when user language equals config language', function () {
            Config::$language = 'english';

            User::$me->language = 'english';

            $result = Language::isDefault();
            expect($result)->toBeTrue();
        });

        it('returns false when user language differs from config language', function () {
            Config::$language = 'english';

            User::$me->language = 'russian';

            $result = Language::isDefault();
            expect($result)->toBeFalse();
        });
    });

    describe('prepareList()', function () {
        it('sets languages in context without userLanguage', function () {
            Config::$modSettings['userLanguage'] = '';
            Config::$language = 'english';

            Language::prepareList();

            expect(Utils::$context['lp_languages'])->toBeArray()
                ->and(Utils::$context['lp_languages'])->toHaveKey(Config::$language);
        });

        it('sets languages in context with userLanguage', function () {
            Config::$modSettings['userLanguage'] = '1';
            Config::$language = 'english';

            User::$me->language = 'russian';

            Language::prepareList();

            expect(Utils::$context['lp_languages'])->toBeArray()
                ->and(Utils::$context['lp_languages'])->toHaveKey(User::$me->language)
                ->and(Utils::$context['lp_languages'])->toHaveKey(Config::$language);
        });
    });
});

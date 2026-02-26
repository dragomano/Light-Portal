<?php

declare(strict_types=1);

use Bugo\Compat\Lang;

beforeEach(function () {
    Lang::$txt = [];
});

describe('__ function', function () {
    describe('simple key without separators', function () {
        it('returns value from Lang::$txt', function () {
            Lang::$txt['hello'] = 'Hello world';

            expect(__('hello'))->toBe('Hello world');
        });

        it('returns empty string for unknown key', function () {
            expect(__('unknown_key'))->toBe('');
        });

        it('passes args to MessageFormatter', function () {
            Lang::$txt['greeting'] = 'Hello, {name}!';

            expect(__('greeting', ['name' => 'John']))->toBe('Hello, John!');
        });

        it('reads from editortxt when var is specified', function () {
            Lang::$editortxt['bold'] = 'Bold';

            expect(__('bold', [], 'editortxt'))->toBe('Bold');
        });
    });

    describe('key with :: separator', function () {
        it('extracts file and key', function () {
            Lang::$txt['page_title'] = 'My Page';

            // load() will be called but txt is already set
            expect(__('SomeLangFile::page_title'))->toBe('My Page');
        });

        it('does not split rest by dot', function () {
            Lang::$txt['group.nested'] = 'Nested value';

            expect(__('SomeLangFile::group.nested'))->toBe('Nested value');
        });

        it(':: takes priority over .', function () {
            Lang::$txt['key'] = 'Correct';

            expect(__('file.name::key'))->toBe('Correct');
        });
    });

    describe('key with . separator', function () {
        it('extracts file and simple key', function () {
            Lang::$txt['page_title'] = 'My Page';

            expect(__('SomeLangFile.page_title'))->toBe('My Page');
        });

        it('resolves nested key when two dots present', function () {
            Lang::$txt['group']['nested'] = 'Deep value';

            expect(__('SomeLangFile.group.nested'))->toBe('Deep value');
        });

        it('returns empty string for unknown nested key', function () {
            expect(__('SomeLangFile.group.missing'))->toBe('');
        });
    });

    describe('edge cases', function () {
        it('is defined only once', function () {
            expect(function_exists('__'))->toBeTrue();
        });

        it('returns empty string for empty key', function () {
            expect(__(''))->toBe('');
        });
    });
});

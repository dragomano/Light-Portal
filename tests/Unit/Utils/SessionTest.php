<?php

declare(strict_types=1);

use LightPortal\Utils\GlobalArray;
use LightPortal\Utils\Session;
use LightPortal\Utils\SessionInterface;

arch()
    ->expect(Session::class)
    ->toExtend(GlobalArray::class)
    ->toImplement(SessionInterface::class);

describe('Session', function () {
    beforeEach(function () {
        $GLOBALS['_SESSION'] = [];
        $_SESSION = &$GLOBALS['_SESSION'];
    });

    afterEach(function () {
        unset($GLOBALS['_SESSION']);
        unset($_SESSION);
    });

    describe('__construct()', function () {
        it('initializes with null key using $_SESSION directly', function () {
            $_SESSION['test_key'] = 'test_value';

            $session = new Session();

            expect($session->get('test_key'))->toBe('test_value');
        });

        it('initializes with key creating array if not exists', function () {
            unset($_SESSION['new_key']);

            new Session('new_key');

            expect($_SESSION['new_key'])->toBeArray();
        });

        it('uses existing session array when key is provided', function () {
            $_SESSION['existing_key'] = ['old' => 'value'];

            $session = new Session('existing_key');

            expect($session->get('old'))->toBe('value');
        });
    });

    describe('withKey()', function () {
        it('returns new Session instance with key', function () {
            $_SESSION['parent_key'] = ['child' => 'data'];

            $parentSession = new Session();
            $childSession = $parentSession->withKey('parent_key');

            expect($childSession)->toBeInstanceOf(Session::class)
                ->and($childSession->get('child'))->toBe('data');
        });

        it('returns independent Session when key does not exist', function () {
            $parentSession = new Session();
            $childSession = $parentSession->withKey('nonexistent');

            expect($childSession)->toBeInstanceOf(Session::class)
                ->and($childSession->all())->toBeEmpty();
        });
    });

    describe('free()', function () {
        it('removes key from storage', function () {
            $_SESSION['to_remove'] = ['key1' => 'value1', 'key2' => 'value2'];

            $session = new Session('to_remove');

            expect($session->get('key1'))->toBe('value1')
                ->and($session->get('key2'))->toBe('value2');

            $session->free('key1');

            expect($session->get('key1'))->toBeNull()
                ->and($session->get('key2'))->toBe('value2');
        });
    });

    describe('inheritance from GlobalArray', function () {
        it('put stores value in session', function () {
            $session = new Session();

            $session->put('test_key', 'test_value');

            expect($_SESSION['test_key'])->toBe('test_value');
        });

        it('all returns all session data', function () {
            $_SESSION['key1'] = 'value1';
            $_SESSION['key2'] = 'value2';

            $session = new Session();

            expect($session->all())->toBe(['key1' => 'value1', 'key2' => 'value2']);
        });

        it('only returns specified keys', function () {
            $_SESSION['key1'] = 'value1';
            $_SESSION['key2'] = 'value2';
            $_SESSION['key3'] = 'value3';

            $session = new Session();

            expect($session->only(['key1', 'key3']))->toBe(['key1' => 'value1', 'key3' => 'value3']);
        });

        it('except returns all except specified keys', function () {
            $_SESSION['key1'] = 'value1';
            $_SESSION['key2'] = 'value2';
            $_SESSION['key3'] = 'value3';

            $session = new Session();

            expect($session->except(['key2']))->toBe(['key1' => 'value1', 'key3' => 'value3']);
        });

        it('has returns true when key exists', function () {
            $_SESSION['existing_key'] = 'value';

            $session = new Session();

            expect($session->has('existing_key'))->toBeTrue()
                ->and($session->has(['existing_key']))->toBeTrue();
        });

        it('has returns false when key does not exist', function () {
            $session = new Session();

            expect($session->has('missing_key'))->toBeFalse()
                ->and($session->has(['missing_key']))->toBeFalse();
        });

        it('hasNot returns true when key does not exist', function () {
            $session = new Session();

            expect($session->hasNot('missing_key'))->toBeTrue();
        });

        it('hasNot returns false when key exists', function () {
            $_SESSION['existing_key'] = 'value';

            $session = new Session();

            expect($session->hasNot('existing_key'))->toBeFalse();
        });

        it('isEmpty returns true for empty value', function () {
            $_SESSION['empty_key'] = '';

            $session = new Session();

            expect($session->isEmpty('empty_key'))->toBeTrue();
        });

        it('isEmpty returns false for non-empty value', function () {
            $_SESSION['non_empty_key'] = 'value';

            $session = new Session();

            expect($session->isEmpty('non_empty_key'))->toBeFalse();
        });

        it('isNotEmpty returns true for non-empty value', function () {
            $_SESSION['non_empty_key'] = 'value';

            $session = new Session();

            expect($session->isNotEmpty('non_empty_key'))->toBeTrue();
        });

        it('isNotEmpty returns false for empty value', function () {
            $_SESSION['empty_key'] = '';

            $session = new Session();

            expect($session->isNotEmpty('empty_key'))->toBeFalse();
        });
    });
});


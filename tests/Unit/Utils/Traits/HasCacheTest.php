<?php

declare(strict_types=1);

use Bugo\Compat\User;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\Traits\HasCache;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->cacheMock = mock(CacheInterface::class);
    $this->cacheMock->shouldReceive('withKey')->andReturnSelf();

    AppMockRegistry::set(CacheInterface::class, $this->cacheMock);

    $this->testClass = new class {
        use HasCache;
    };

    $this->reflection = new ReflectionAccessor($this->testClass);
});

afterEach(function () {
    AppMockRegistry::clear(CacheInterface::class);
});

describe('HasCache::cache()', function () {
    it('returns cache interface without key', function () {
        $result = $this->testClass->cache(null);

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });

    it('returns cache interface with key', function () {
        $result = $this->testClass->cache('test_key');

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });
});

describe('HasCache::langCache()', function () {
    it('returns cache interface with lang suffix', function () {
        User::$me->id = 1;
        User::$me->language = 'english';

        $result = $this->testClass->langCache('test');

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });

    it('returns cache interface with null key', function () {
        User::$me->id = 1;
        User::$me->language = 'english';

        $result = $this->testClass->langCache(null);

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });
});

describe('HasCache::userCache()', function () {
    it('returns cache interface with user suffix', function () {
        User::$me->id = 1;
        User::$me->language = 'english';

        $result = $this->testClass->userCache('test');

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });

    it('returns cache interface with null key for userCache', function () {
        User::$me->id = 1;
        User::$me->language = 'english';

        $result = $this->testClass->userCache(null);

        expect($result)->toBeInstanceOf(CacheInterface::class);
    });
});

describe('HasCache::appendUserSuffix()', function () {
    it('appends user suffix to key', function () {
        User::$me->id = 42;

        $result = $this->reflection->callMethod('appendUserSuffix', ['my_key']);

        expect($result)->toBe('my_key_u42');
    });

    it('returns null for null key', function () {
        User::$me->id = 42;

        $result = $this->reflection->callMethod('appendUserSuffix', [null]);

        expect($result)->toBeNull();
    });

    it('returns null for empty key', function () {
        User::$me->id = 42;

        $result = $this->reflection->callMethod('appendUserSuffix', ['']);

        expect($result)->toBeNull();
    });
});

describe('HasCache::appendLangSuffix()', function () {
    it('appends lang suffix to key', function () {
        User::$me->id = 42;
        User::$me->language = 'russian';

        $result = $this->reflection->callMethod('appendLangSuffix', ['my_key']);

        expect($result)->toBe('my_key_u42_russian');
    });

    it('returns null for null key', function () {
        User::$me->id = 42;
        User::$me->language = 'russian';

        $result = $this->reflection->callMethod('appendLangSuffix', [null]);

        expect($result)->toBeNull();
    });
});

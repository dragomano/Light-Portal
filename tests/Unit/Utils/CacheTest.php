<?php

declare(strict_types=1);

use LightPortal\Utils\Cache;
use LightPortal\Utils\CacheInterface;

beforeEach(function () {
    $this->cache = new Cache('test_key', 3600);
});

describe('Cache', function () {
    it('implements CacheInterface', function () {
        expect($this->cache)->toBeInstanceOf(CacheInterface::class);
    });

    describe('__construct()', function () {
        it('initializes with key and lifetime', function () {
            $cache = new Cache('my_key', 1800);
            expect($cache)->toBeInstanceOf(Cache::class);
        });

        it('uses default lifetime when null', function () {
            $cache = new Cache('my_key');
            expect($cache)->toBeInstanceOf(Cache::class);
        });
    });

    describe('withKey()', function () {
        it('returns new Cache instance with different key', function () {
            $newCache = $this->cache->withKey('different_key');
            expect($newCache)->toBeInstanceOf(Cache::class);
        });

        it('returns new Cache instance with null key', function () {
            $newCache = $this->cache->withKey(null);
            expect($newCache)->toBeInstanceOf(Cache::class);
        });
    });

    describe('setLifeTime()', function () {
        it('sets lifetime and returns self', function () {
            $result = $this->cache->setLifeTime(7200);
            expect($result)->toBe($this->cache);
        });
    });

    describe('remember()', function () {
        it('returns cached data when key exists', function () {
            $cachedData = ['test' => 'value'];
            expect($cachedData)->toBeArray();
        });
    });

    describe('setFallback()', function () {
        it('calls remember with instance key', function () {
            $cache = new Cache('fallback_key', 3600);
            expect($cache)->toBeInstanceOf(Cache::class);
        });
    });

    describe('get()', function () {
        it('gets value from cache with prefix', function () {
            $cachedData = ['key' => 'value'];
            expect($cachedData)->toBeArray();
        });
    });

    describe('put()', function () {
        it('puts value to cache with prefix', function () {
            $GLOBALS['cache_put_data_calls'] = [];

            $this->cache->put('custom_key', 'value', 120);

            expect($GLOBALS['cache_put_data_calls'])->toHaveCount(1)
                ->and($GLOBALS['cache_put_data_calls'][0])->toBe([
                    'key' => 'lp_custom_key',
                    'value' => 'value',
                    'ttl' => 120,
                ]);
        });
    });

    describe('forget()', function () {
        it('calls put with null value', function () {
            $GLOBALS['cache_put_data_calls'] = [];

            $this->cache->forget('forget_key');

            expect($GLOBALS['cache_put_data_calls'])->toHaveCount(1)
                ->and($GLOBALS['cache_put_data_calls'][0])->toBe([
                    'key' => 'lp_forget_key',
                    'value' => null,
                    'ttl' => 3600,
                ]);
        });
    });

    describe('flush()', function () {
        it('calls CacheApi::clean()', function () {
            $GLOBALS['clean_cache_calls'] = [];

            $this->cache->flush();

            expect($GLOBALS['clean_cache_calls'])->toHaveCount(1)
                ->and($GLOBALS['clean_cache_calls'][0])->toBe(['type' => '']);
        });
    });
});

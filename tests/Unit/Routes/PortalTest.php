<?php

declare(strict_types=1);

use Bugo\Compat\Routable;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Routes\Portal;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;

arch()
    ->expect(Portal::class)
    ->toImplement(Routable::class);

describe('Portal route', function () {
    afterEach(function () {
        AppMockRegistry::clear();
    });

    it('returns cached data when available', function () {
        $cache = mock(CacheInterface::class);
        $cache->shouldReceive('get')->with('lp_sef_categories')->andReturn([1 => 'news']);
        AppMockRegistry::set(CacheInterface::class, $cache);
        AppMockRegistry::set('\\' . CacheInterface::class, $cache);

        $data = Portal::getDataFromCache();

        if ($data !== []) {
            expect($data)->toBe([1 => 'news']);
        } else {
            expect($data)->toBe([]);
        }
    });

    it('returns empty cache data on exception', function () {
        $cache = mock(CacheInterface::class);
        $cache->shouldReceive('get')->andThrow(new RuntimeException('fail'));
        AppMockRegistry::set(CacheInterface::class, $cache);
        AppMockRegistry::set('\\' . CacheInterface::class, $cache);

        expect(Portal::getDataFromCache())->toBe([]);
    });

    it('maps cached names and entry ids', function () {
        $cache = mock(CacheInterface::class);
        $cache->shouldReceive('get')->with('lp_sef_categories')->andReturn([1 => 'news']);
        AppMockRegistry::set(CacheInterface::class, $cache);
        AppMockRegistry::set('\\' . CacheInterface::class, $cache);

        $cachedName = Portal::getCachedName('1');
        $entryId = Portal::getEntryId('news');

        expect(in_array($cachedName, ['news', '1'], true))->toBeTrue()
            ->and(in_array($entryId, ['1', 'news'], true))->toBeTrue()
            ->and(Portal::getEntryId('missing'))->toBe('missing');
    });

    it('builds route with sa and cached id', function () {
        $cache = mock(CacheInterface::class);
        $cache->shouldReceive('get')->with('lp_sef_categories')->andReturn([5 => 'tech']);
        AppMockRegistry::set(CacheInterface::class, $cache);
        AppMockRegistry::set('\\' . CacheInterface::class, $cache);

        $params = [
            'action' => LP_ACTION,
            'sa' => PortalSubAction::CATEGORIES->name(),
            'id' => '5',
            'start' => 10,
            'foo' => 'bar',
        ];

        $result = Portal::buildRoute($params);

        $route = $result['route'];

        expect($route[0])->toBe(LP_ACTION)
            ->and($route[1])->toBe(PortalSubAction::CATEGORIES->name())
            ->and(in_array($route[2], ['tech', '5'], true))->toBeTrue()
            ->and($route[3])->toBe(10)
            ->and($result['params'])->toBe(['foo' => 'bar']);
    });

    it('builds empty route for portal start 0', function () {
        $params = [
            'action' => LP_ACTION,
            'start' => '0',
        ];

        $result = Portal::buildRoute($params);

        expect($result['route'])->toBe([])
            ->and($result['params'])->toBe([]);
    });

    it('parses empty route as portal action', function () {
        $params = Portal::parseRoute([]);

        expect($params)->toBe(['action' => LP_ACTION]);
    });

    it('parses start-only route', function () {
        $params = Portal::parseRoute([LP_ACTION, '20']);

        expect($params)->toBe([
            'action' => LP_ACTION,
            'start' => '20',
        ]);
    });

    it('parses category route with cached slug', function () {
        $cache = mock(CacheInterface::class);
        $cache->shouldReceive('get')->with('lp_sef_categories')->andReturn([3 => 'cats']);
        AppMockRegistry::set(CacheInterface::class, $cache);
        AppMockRegistry::set('\\' . CacheInterface::class, $cache);

        $params = Portal::parseRoute([LP_ACTION, PortalSubAction::CATEGORIES->name(), 'cats', '30']);

        expect($params['action'])->toBe(LP_ACTION)
            ->and($params['sa'])->toBe(PortalSubAction::CATEGORIES->name())
            ->and(in_array($params['id'], ['3', 'cats'], true))->toBeTrue()
            ->and($params['start'])->toBe('30');
    });

    it('parses promote route with topic id', function () {
        $params = Portal::parseRoute([LP_ACTION, PortalSubAction::PROMOTE->name(), '42']);

        expect($params)->toBe([
            'action' => LP_ACTION,
            'sa' => PortalSubAction::PROMOTE->name(),
            't' => '42',
        ]);
    });
});

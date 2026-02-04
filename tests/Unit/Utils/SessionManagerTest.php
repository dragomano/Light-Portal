<?php

declare(strict_types=1);

use Bugo\Compat\User;
use LightPortal\Enums\Status;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\Utils\SessionInterface;
use LightPortal\Utils\SessionManager;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(SessionManager::class)
    ->toBeInvokable();

beforeEach(function () {
    $this->sessionMock = mock(SessionInterface::class);
    $this->sessionMock->shouldReceive('withKey')->andReturnSelf();

    AppMockRegistry::set(SessionInterface::class, $this->sessionMock);
});

describe('SessionManager::getCountConfig()', function () {
    it('returns config with user-specific cache keys when not admin', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['active_pages']['cache_key'])->toBe('active_pages_u1')
            ->and($config['my_pages']['cache_key'])->toBe('my_pages_u1');
    });

    it('includes all required count types', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect(array_keys($config))->toBe([
            'active_blocks',
            'active_pages',
            'my_pages',
            'unapproved_pages',
            'deleted_pages',
            'active_categories',
            'active_tags'
        ]);
    });

    it('includes correct conditions for my_pages', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['my_pages']['conditions'])->toBe([
            'author_id'  => 1,
            'deleted_at' => 0
        ]);
    });

    it('includes correct conditions for unapproved_pages', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['unapproved_pages']['conditions'])->toBe([
            'status'     => Status::UNAPPROVED->value,
            'deleted_at' => 0
        ]);
    });

    it('includes correct conditions for deleted_pages', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['deleted_pages']['conditions'])->toBe(['deleted_at != ?' => 0]);
    });

    it('includes correct conditions for active_categories', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['active_categories']['conditions'])->toBe(['status' => Status::ACTIVE->value]);
    });

    it('includes correct conditions for active_tags', function () {
        User::$me->id = 1;

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            mock(PageRepositoryInterface::class),
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $config = $reflection->callMethod('getCountConfig');

        expect($config['active_tags']['conditions'])->toBe(['status' => Status::ACTIVE->value]);
    });
});

describe('SessionManager::__invoke()', function () {
    it('returns array of counts', function () {
        User::$me->id = 1;

        $blockRepository = mock(BlockRepositoryInterface::class);
        $blockRepository->shouldReceive('getTotalCount')
            ->with('', ['status' => Status::ACTIVE->value])
            ->once()
            ->andReturn(10);

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['author_id' => 1, 'deleted_at' => 0])
            ->once()
            ->andReturn(15);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['status' => Status::ACTIVE->value, 'deleted_at' => 0, 'author_id' => 1])
            ->once()
            ->andReturn(5);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['status' => Status::UNAPPROVED->value, 'deleted_at' => 0])
            ->once()
            ->andReturn(2);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['deleted_at != ?' => 0])
            ->once()
            ->andReturn(1);

        $categoryRepository = mock(CategoryRepositoryInterface::class);
        $categoryRepository->shouldReceive('getTotalCount')
            ->with('', ['status' => Status::ACTIVE->value])
            ->once()
            ->andReturn(3);

        $tagRepository = mock(TagRepositoryInterface::class);
        $tagRepository->shouldReceive('getTotalCount')
            ->with('', ['status' => Status::ACTIVE->value])
            ->once()
            ->andReturn(7);

        $sessionCallCount = 0;
        $this->sessionMock->shouldReceive('get')->andReturnUsing(function () use (&$sessionCallCount) {
            $sessionCallCount++;
            if ($sessionCallCount % 2 === 1) {
                return null;
            }
            return match (intdiv($sessionCallCount - 2, 2)) {
                0 => 10,
                1 => 5,
                2 => 15,
                3 => 2,
                4 => 1,
                5 => 3,
                6 => 7,
                default => 0
            };
        });
        $this->sessionMock->shouldReceive('put')->andReturn(null);

        $manager = new SessionManager(
            $blockRepository,
            $pageRepository,
            $categoryRepository,
            $tagRepository
        );

        $result = $manager();

        expect($result['active_blocks'])->toBe(10)
            ->and($result['active_pages'])->toBe(5)
            ->and($result['my_pages'])->toBe(15)
            ->and($result['unapproved_pages'])->toBe(2)
            ->and($result['deleted_pages'])->toBe(1)
            ->and($result['active_categories'])->toBe(3)
            ->and($result['active_tags'])->toBe(7);
    });

    it('returns cached counts from session', function () {
        User::$me->id = 1;

        $blockRepository = mock(BlockRepositoryInterface::class);
        $blockRepository->shouldReceive('getTotalCount')->never();

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')->never();

        $categoryRepository = mock(CategoryRepositoryInterface::class);
        $categoryRepository->shouldReceive('getTotalCount')->never();

        $tagRepository = mock(TagRepositoryInterface::class);
        $tagRepository->shouldReceive('getTotalCount')->never();

        $this->sessionMock->shouldReceive('get')->andReturn(42);

        $manager = new SessionManager(
            $blockRepository,
            $pageRepository,
            $categoryRepository,
            $tagRepository
        );

        $result = $manager();

        expect($result['active_blocks'])->toBe(42)
            ->and($result['active_pages'])->toBe(42);
    });
});

describe('SessionManager::getCount()', function () {
    it('returns count for specific type', function () {
        User::$me->id = 1;

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['author_id' => 1, 'deleted_at' => 0])
            ->andReturn(15);

        $this->sessionMock->shouldReceive('get')->andReturnValues([null, 15]);
        $this->sessionMock->shouldReceive('put')->andReturn(null);

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            $pageRepository,
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $result = $reflection->callMethod('getCount', ['my_pages']);

        expect($result)->toBe(15);
    });

    it('uses cache_key from config if present', function () {
        User::$me->id = 1;

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', Mockery::any())
            ->andReturn(20);

        $this->sessionMock->shouldReceive('get')->with('active_pages_u1')->andReturnValues([null, 20]);
        $this->sessionMock->shouldReceive('put')->andReturn(null);

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            $pageRepository,
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $result = $reflection->callMethod('getCount', ['active_pages']);

        expect($result)->toBe(20);
    });
});

describe('SessionManager::getCachedCount()', function () {
    it('returns cached value if present', function () {
        User::$me->id = 1;

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')->never();

        $this->sessionMock->shouldReceive('get')->andReturn(100);

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            $pageRepository,
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $result = $reflection->callMethod('getCachedCount', ['test_key', $pageRepository, []]);

        expect($result)->toBe(100);
    });

    it('fetches from repository and caches when not cached', function () {
        User::$me->id = 1;

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')
            ->with('', ['author_id' => 1, 'deleted_at' => 0])
            ->andReturn(25);

        $this->sessionMock->shouldReceive('get')->andReturnValues([null, 25]);
        $this->sessionMock->shouldReceive('put')->with('my_pages_u1', 25)->andReturn(null);

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            $pageRepository,
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $result = $reflection->callMethod('getCachedCount', ['my_pages_u1', $pageRepository, ['author_id' => 1, 'deleted_at' => 0]]);

        expect($result)->toBe(25);
    });

    it('returns 0 when cache is null and repository returns 0', function () {
        User::$me->id = 1;

        $pageRepository = mock(PageRepositoryInterface::class);
        $pageRepository->shouldReceive('getTotalCount')->andReturn(0);

        $this->sessionMock->shouldReceive('get')->andReturnValues([null, 0]);
        $this->sessionMock->shouldReceive('put')->andReturn(null);

        $manager = new SessionManager(
            mock(BlockRepositoryInterface::class),
            $pageRepository,
            mock(CategoryRepositoryInterface::class),
            mock(TagRepositoryInterface::class)
        );

        $reflection = new ReflectionAccessor($manager);
        $result = $reflection->callMethod('getCachedCount', ['empty_key', $pageRepository, []]);

        expect($result)->toBe(0);
    });
});

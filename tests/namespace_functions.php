<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Bugo\LightPortal\Database\Operations\PortalSelect;
use Bugo\LightPortal\Events\EventManager;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Repositories\CategoryRepositoryInterface;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\Repositories\TagRepositoryInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Bugo\LightPortal\UI\View;
use Bugo\LightPortal\Utils\CacheInterface;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Tests\AppMockRegistry;
use Mockery;

if (! function_exists('Bugo\\LightPortal\\app')) {
    function app(string $service = ''): mixed
    {
        if ($mock = AppMockRegistry::get($service)) {
            return $mock;
        }

        if (str_contains($service, 'CacheInterface')) {
            return new class implements CacheInterface {
                public function withKey(?string $key): CacheInterface
                {
                    return $this;
                }

                public function setLifeTime(int $lifeTime): CacheInterface
                {
                    return $this;
                }

                public function remember(string $key, callable $callback, ?int $time = null): mixed
                {
                    return $callback();
                }

                public function setFallback(callable $callback): null
                {
                    return null;
                }

                public function get(string $key, int $time): null
                {
                    return null;
                }

                public function put(string $key, mixed $value, int $time): void
                {
                }

                public function forget(string $key): void
                {
                }

                public function flush(): void
                {
                }
            };
        } elseif (str_contains($service, 'PortalSqlInterface')) {
            $mock = Mockery::mock(PortalSqlInterface::class);
            $selectMock = Mockery::mock(PortalSelect::class);
            $selectMock->shouldReceive('from')->andReturnSelf();
            $selectMock->shouldReceive('columns')->andReturnSelf();
            $selectMock->shouldReceive('join')->andReturnSelf();
            $selectMock->shouldReceive('where')->andReturnSelf();
            $selectMock->shouldReceive('order')->andReturnSelf();
            $selectMock->shouldIgnoreMissing();
            $mock->shouldReceive('select')->andReturn($selectMock);
            $resultMock = Mockery::mock(ResultInterface::class);
            $resultMock->shouldReceive('current')->andReturn(['id_member' => []]);
            $resultMock->shouldReceive('valid')->andReturn(false);
            $resultMock->shouldReceive('next')->andReturn(null);
            $resultMock->shouldReceive('key')->andReturn(0);
            $resultMock->shouldReceive('rewind')->andReturn(null);
            $mock->shouldReceive('execute')->andReturn($resultMock);
            return $mock;
        } elseif (str_contains($service, 'EventManagerFactory')) {
            if (isset($GLOBALS['event_manager_mock'])) {
                return new class($GLOBALS['event_manager_mock']) {
                    private EventManager $eventManagerMock;

                    public function __construct($eventManagerMock) {
                        $this->eventManagerMock = $eventManagerMock;
                    }

                    public function __invoke(): EventManager
                    {
                        return $this->eventManagerMock;
                    }
                };
            }

            $eventManagerMock = Mockery::mock(EventManager::class);
            $eventManagerMock->shouldReceive('dispatch')->byDefault()->andReturn(null);

            return new class($eventManagerMock) {
                private EventManager $eventManagerMock;

                public function __construct($eventManagerMock) {
                    $this->eventManagerMock = $eventManagerMock;
                }

                public function __invoke(): EventManager
                {
                    return $this->eventManagerMock;
                }
            };
        } elseif (str_contains($service, 'SelectRenderer')) {
            if ($mock = AppMockRegistry::get(SelectRenderer::class)) {
                return $mock;
            }

            return null;
        } elseif (str_contains($service, 'View')) {
            $mockView = Mockery::mock('overload:' . View::class);
            $mockView->shouldReceive('render')->andReturn('<div>rendered</div>');

            return $mockView;
        } elseif (str_contains($service, 'CategoryList')) {
            if ($mock = AppMockRegistry::get(CategoryList::class)) {
                return $mock;
            }

            $mockRepo = Mockery::mock(CategoryRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new CategoryList($mockRepo);
        } elseif (str_contains($service, 'TagList')) {
            if ($mock = AppMockRegistry::get(TagList::class)) {
                return $mock;
            }

            $mockRepo = Mockery::mock(TagRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new TagList($mockRepo);
        } elseif (str_contains($service, 'PageList')) {
            if ($mock = AppMockRegistry::get(PageList::class)) {
                return $mock;
            }

            $mockRepo = Mockery::mock(PageRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new PageList($mockRepo);
        } elseif (str_contains($service, 'RequestInterface')) {
            if ($mock = AppMockRegistry::get(RequestInterface::class)) {
                return $mock;
            }

            $mock = Mockery::mock(RequestInterface::class);
            $mock->shouldReceive('is')->andReturn(false);
            $mock->shouldReceive('has')->andReturn(false);
            $mock->shouldReceive('url')->andReturn('');
            $mock->shouldReceive('input')->andReturn('');
            $mock->shouldIgnoreMissing();

            return $mock;
        }

        return null;
    }
}

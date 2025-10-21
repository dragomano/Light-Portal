<?php

declare(strict_types=1);

namespace LightPortal;

use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventManager;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\PageList;
use LightPortal\Lists\TagList;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\View;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Mockery;
use Tests\AppMockRegistry;

if (! function_exists('LightPortal\\app')) {
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
            $selectMock = Mockery::mock(PortalSelect::class);
            $selectMock->shouldReceive('from')->andReturnSelf();
            $selectMock->shouldReceive('columns')->andReturnSelf();
            $selectMock->shouldReceive('join')->andReturnSelf();
            $selectMock->shouldReceive('where')->andReturnSelf();
            $selectMock->shouldReceive('order')->andReturnSelf();
            $selectMock->shouldIgnoreMissing();

            $resultMock = Mockery::mock(PortalResultInterface::class);
            $resultMock->shouldReceive('current')->andReturn(['id_member' => []]);
            $resultMock->shouldReceive('valid')->andReturn(false);
            $resultMock->shouldReceive('next')->andReturn(null);
            $resultMock->shouldReceive('key')->andReturn(0);
            $resultMock->shouldReceive('rewind')->andReturn(null);

            $mock = Mockery::mock(PortalSqlInterface::class);
            $mock->shouldReceive('select')->andReturn($selectMock);
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
        } elseif (str_contains($service, 'ResponseInterface')) {
            if ($mock = AppMockRegistry::get(ResponseInterface::class)) {
                return $mock;
            }

            $mock = Mockery::mock(ResponseInterface::class);
            $mock->shouldReceive('exit')->andReturn(null);

            return $mock;
        }

        return null;
    }
}

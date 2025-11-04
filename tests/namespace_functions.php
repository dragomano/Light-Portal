<?php

declare(strict_types=1);

namespace LightPortal;

use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventManager;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\ListInterface;
use LightPortal\Lists\PageList;
use LightPortal\Lists\TagList;
use LightPortal\Renderers\Blade;
use LightPortal\Renderers\PurePHP;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\ViewInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Utils\SessionInterface;
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

                public function get(string $key, int $time = null): null
                {
                    return null;
                }

                public function put(string $key, mixed $value, int $time = null): void {}

                public function forget(?string $key = null): void {}

                public function flush(): void {}
            };
        } elseif (str_contains($service, 'PortalSqlInterface')) {
            $selectMock = mock(PortalSelect::class);
            $selectMock->shouldReceive('from')->andReturnSelf();
            $selectMock->shouldReceive('columns')->andReturnSelf();
            $selectMock->shouldReceive('join')->andReturnSelf();
            $selectMock->shouldReceive('where')->andReturnSelf();
            $selectMock->shouldReceive('order')->andReturnSelf();
            $selectMock->shouldIgnoreMissing();

            $resultMock = mock(PortalResultInterface::class);
            $resultMock->shouldReceive('current')->andReturn(['id_member' => []]);
            $resultMock->shouldReceive('valid')->andReturn(false);
            $resultMock->shouldReceive('next')->andReturn(null);
            $resultMock->shouldReceive('key')->andReturn(0);
            $resultMock->shouldReceive('rewind')->andReturn(null);

            $mock = mock(PortalSqlInterface::class);
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

            $eventManagerMock = mock(EventManager::class);
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
        } elseif (str_contains($service, 'ViewInterface')) {
            if ($mock = AppMockRegistry::get(ViewInterface::class)) {
                return $mock;
            }

            $mockView = mock(ViewInterface::class);
            $mockView->shouldReceive('setTemplateDir')->byDefault()->andReturn($mockView);
            $mockView->shouldReceive('render')->byDefault()->andReturn('<div>rendered</div>');

            return $mockView;
        } elseif (str_contains($service, 'Blade')) {
            if ($mock = AppMockRegistry::get('Blade')) {
                return $mock;
            }

            $mock = mock(Blade::class);
            $mock->shouldReceive('setTemplateDir')->byDefault()->andReturn($mock);
            $mock->shouldReceive('setCustomDir')->byDefault()->andReturn($mock);
            $mock->shouldReceive('render')->byDefault()->andReturn('<div>blade rendered</div>');

            return $mock;
        } elseif (str_contains($service, 'PurePHP')) {
            if ($mock = AppMockRegistry::get('PurePHP')) {
                return $mock;
            }

            $mock = mock(PurePHP::class);
            $mock->shouldReceive('setTemplateDir')->byDefault()->andReturn($mock);
            $mock->shouldReceive('setCustomDir')->byDefault()->andReturn($mock);
            $mock->shouldReceive('render')->byDefault()->andReturn('<div>pure php rendered</div>');

            return $mock;
        } elseif (str_contains($service, 'CategoryList')) {
            if ($mock = AppMockRegistry::get(CategoryList::class)) {
                return $mock;
            }

            $mockRepo = mock(CategoryRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new CategoryList($mockRepo);
        } elseif (str_contains($service, 'TagList')) {
            if ($mock = AppMockRegistry::get(TagList::class)) {
                return $mock;
            }

            $mockRepo = mock(TagRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new TagList($mockRepo);
        } elseif (str_contains($service, 'PluginList')) {
            if ($mock = AppMockRegistry::get('PluginList')) {
                return $mock;
            }

            return new class implements ListInterface {
                public function __invoke(): array
                {
                    return ['TestPlugin'];
                }
            };
        } elseif (str_contains($service, 'PageList')) {
            if ($mock = AppMockRegistry::get(PageList::class)) {
                return $mock;
            }

            $mockRepo = mock(PageRepositoryInterface::class);
            $mockRepo->shouldReceive('getTotalCount')->andReturn(0);
            $mockRepo->shouldReceive('getAll')->andReturn([]);
            $mockRepo->shouldReceive('getTranslationFilter')->andReturn('');

            return new PageList($mockRepo);
        } elseif (str_contains($service, 'RequestInterface')) {
            if ($mock = AppMockRegistry::get(RequestInterface::class)) {
                return $mock;
            }

            $mock = mock(RequestInterface::class);
            $mock->shouldReceive('is')->andReturn(false);
            $mock->shouldReceive('has')->andReturn(false);
            $mock->shouldReceive('hasNot')->andReturn(true);
            $mock->shouldReceive('put')->andReturn(null);
            $mock->shouldReceive('url')->andReturn('');
            $mock->shouldReceive('input')->andReturn('');
            $mock->shouldIgnoreMissing();

            return $mock;
        } elseif (str_contains($service, 'ResponseInterface')) {
            if ($mock = AppMockRegistry::get(ResponseInterface::class)) {
                return $mock;
            }

            $mock = mock(ResponseInterface::class);
            $mock->shouldReceive('exit')->andReturn(null);

            return $mock;
        } elseif (str_contains($service, 'SessionInterface')) {
            if ($mock = AppMockRegistry::get(SessionInterface::class)) {
                return $mock;
            }

            $mock = mock(SessionInterface::class);
            $mock->shouldReceive('withKey')->andReturnSelf();
            $mock->shouldReceive('isEmpty')->andReturn(false);
            $mock->shouldReceive('get')->andReturn('');
            $mock->shouldReceive('put')->andReturn(null);
            $mock->shouldIgnoreMissing();

            return $mock;
        } elseif (str_contains($service, 'PostInterface')) {
            if ($mock = AppMockRegistry::get('PostInterface')) {
                return $mock;
            }

            $mock = mock(PostInterface::class);
            $mock->shouldReceive('set')->andReturn(null);
            $mock->shouldReceive('get')->andReturn('');
            $mock->shouldReceive('all')->andReturn([]);
            $mock->shouldReceive('has')->andReturn(false);
            $mock->shouldReceive('hasNot')->andReturn(true);
            $mock->shouldReceive('put')->andReturn(null);
            $mock->shouldReceive('only')->andReturn([]);
            $mock->shouldReceive('except')->andReturn([]);
            $mock->shouldReceive('isEmpty')->andReturn(false);
            $mock->shouldReceive('isNotEmpty')->andReturn(false);
            $mock->shouldIgnoreMissing();

            return $mock;
        }

        return null;
    }
}

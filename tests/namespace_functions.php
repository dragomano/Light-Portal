<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\CacheInterface;
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
        } elseif (str_contains($service, 'EventManagerFactory')) {
            // Check if we have a test-specific event manager mock
            if (isset($GLOBALS['event_manager_mock'])) {
                return new class($GLOBALS['event_manager_mock']) {
                    private $eventManagerMock;

                    public function __construct($eventManagerMock) {
                        $this->eventManagerMock = $eventManagerMock;
                    }

                    public function __invoke() {
                        return $this->eventManagerMock;
                    }
                };
            }

            $eventManagerMock = Mockery::mock('Bugo\LightPortal\Events\EventManager');
            $eventManagerMock->shouldReceive('dispatch')->byDefault()->andReturn(null);

            return new class($eventManagerMock) {
                private $eventManagerMock;

                public function __construct($eventManagerMock) {
                    $this->eventManagerMock = $eventManagerMock;
                }

                public function __invoke() {
                    return $this->eventManagerMock;
                }
            };
        }

        return null;
    }
}

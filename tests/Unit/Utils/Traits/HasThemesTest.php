<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\Traits\HasThemes;
use Tests\AppMockRegistry;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::THEMES->value)->execute();

    $this->sql = new PortalSql($adapter);

    $mockCache = new class implements CacheInterface {
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

        public function get(string $key, ?int $time = null): null
        {
            return null;
        }

        public function put(string $key, mixed $value, ?int $time = null): void
        {
        }

        public function forget(string $key): void
        {
        }

        public function flush(): void
        {
        }
    };

    AppMockRegistry::set(CacheInterface::class, $mockCache);

    $this->testClass = new class($this->sql) {
        use HasThemes;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
        {
            $this->sql = $sql;
        }

        public function callIsDarkTheme(?string $option): bool
        {
            return $this->isDarkTheme($option);
        }

        public function callGetForumThemes(): array
        {
            return $this->getForumThemes();
        }
    };

    $themeCurrent = new stdClass();
    $themeCurrent->settings = ['theme_id' => 1];
    Theme::$current = $themeCurrent;

    Config::$modSettings['knownThemes'] = '1';
});

it('returns false for empty option in isDarkTheme', function () {
    $result = $this->testClass->callIsDarkTheme(null);

    expect($result)->toBeFalse();
});

it('returns false for empty string option in isDarkTheme', function () {
    $result = $this->testClass->callIsDarkTheme('');

    expect($result)->toBeFalse();
});

it('returns true when theme id is in dark themes list', function () {
    $result = $this->testClass->callIsDarkTheme('1,2,3');

    expect($result)->toBeTrue();
});

it('returns false when theme id is not in dark themes list', function () {
    $result = $this->testClass->callIsDarkTheme('2,3,4');

    expect($result)->toBeFalse();
});

it('gets forum themes from database when not cached', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO themes (id_theme, id_member, variable, value) VALUES
        (1, 0, 'name', 'Default Theme')
    ")->execute();

    $result = $this->testClass->callGetForumThemes();

    expect($result)->toHaveKey('1')
        ->and($result['1'])->toBe('Default Theme');
});

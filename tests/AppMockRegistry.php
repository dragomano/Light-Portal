<?php

declare(strict_types=1);

namespace Tests;

class AppMockRegistry
{
    private static array $mocks = [];

    public static function set(string $service, $mock): void
    {
        self::$mocks[$service] = $mock;
    }

    public static function get(string $service)
    {
        return self::$mocks[$service] ?? null;
    }

    public static function clear(): void
    {
        self::$mocks = [];
    }
}

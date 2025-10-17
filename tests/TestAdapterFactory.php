<?php

declare(strict_types=1);

namespace Tests;

use Bugo\LightPortal\Database\PortalAdapter;
use Bugo\LightPortal\Database\PortalAdapterInterface;

class TestAdapterFactory
{
    public static function create(): PortalAdapterInterface
    {
        return new PortalAdapter([
            'driver'   => 'Pdo_Sqlite',
            'database' => ':memory:',
        ]);
    }
}

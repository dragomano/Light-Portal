<?php

declare(strict_types=1);

namespace Tests;

use LightPortal\Database\PortalAdapter;
use LightPortal\Database\PortalAdapterInterface;

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

<?php

declare(strict_types=1);

use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\Traits\HasPortalSql;

beforeEach(function () {
    $this->testClass = new class {
        use HasPortalSql;
    };
});

it('returns PortalSqlInterface instance', function () {
    $result = $this->testClass->getPortalSql();

    expect($result)->toBeInstanceOf(PortalSqlInterface::class);
});

<?php declare(strict_types=1);

use Bugo\LightPortal\Migrations\Operations\PortalInsert;

describe('PortalInsert', function () {
    it('constructs with prefix', function () {
        $insert = new PortalInsert('test', 'prefix_');

        expect($insert)->toBeInstanceOf(PortalInsert::class);
    });

    it('adds prefix to string table in into', function () {
        $insert = new PortalInsert('test', 'prefix_');

        $result = $insert->into('users');

        expect($result)->toBeInstanceOf(PortalInsert::class);

        $reflection = new ReflectionClass($insert);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($insert))->toBe('prefix_users');
    });

    it('does not add prefix when prefix is empty', function () {
        $insert = new PortalInsert('test', '');

        $insert->into('users');

        $reflection = new ReflectionClass($insert);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($insert))->toBe('users');
    });
});

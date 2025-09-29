<?php declare(strict_types=1);

use Bugo\LightPortal\Migrations\Operations\PortalUpdate;

describe('PortalUpdate', function () {
    it('constructs with prefix', function () {
        $update = new PortalUpdate('test', 'prefix_');

        expect($update)->toBeInstanceOf(PortalUpdate::class);
    });

    it('adds prefix to string table in table', function () {
        $update = new PortalUpdate('test', 'prefix_');

        $result = $update->table('users');

        expect($result)->toBeInstanceOf(PortalUpdate::class);

        $reflection = new ReflectionClass($update);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($update))->toBe('prefix_users');
    });

    it('does not add prefix when prefix is empty in table', function () {
        $update = new PortalUpdate('test', '');

        $update->table('users');

        $reflection = new ReflectionClass($update);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($update))->toBe('users');
    });
});

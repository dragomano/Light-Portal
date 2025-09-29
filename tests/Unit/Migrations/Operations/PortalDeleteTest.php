<?php declare(strict_types=1);

use Bugo\LightPortal\Migrations\Operations\PortalDelete;

describe('PortalDelete', function () {
    it('constructs with prefix', function () {
        $delete = new PortalDelete('test', 'prefix_');

        expect($delete)->toBeInstanceOf(PortalDelete::class);
    });

    it('adds prefix to string table in from', function () {
        $delete = new PortalDelete('test', 'prefix_');

        $result = $delete->from('users');

        expect($result)->toBeInstanceOf(PortalDelete::class);

        $reflection = new ReflectionClass($delete);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($delete))->toBe('prefix_users');
    });

    it('does not add prefix when prefix is empty', function () {
        $delete = new PortalDelete('test', '');

        $delete->from('users');

        $reflection = new ReflectionClass($delete);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($delete))->toBe('users');
    });
});

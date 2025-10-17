<?php declare(strict_types=1);

use Bugo\LightPortal\Database\Operations\PortalDelete;
use Tests\ReflectionAccessor;

describe('PortalDelete', function () {
    it('constructs with prefix', function () {
        $delete = new PortalDelete('test', 'prefix_');

        expect($delete)->toBeInstanceOf(PortalDelete::class);
    });

    it('adds prefix to string table in from', function () {
        $delete = new PortalDelete('test', 'prefix_');

        $result = $delete->from('users');

        expect($result)->toBeInstanceOf(PortalDelete::class);

        $reflection = new ReflectionAccessor($delete);
        $tableProperty = $reflection->getProtectedProperty('table');

        expect($tableProperty)->toBe('prefix_users');
    });

    it('does not add prefix when prefix is empty', function () {
        $delete = new PortalDelete('test', '');

        $delete->from('users');

        $reflection = new ReflectionAccessor($delete);
        $tableProperty = $reflection->getProtectedProperty('table');

        expect($tableProperty)->toBe('users');
    });
});

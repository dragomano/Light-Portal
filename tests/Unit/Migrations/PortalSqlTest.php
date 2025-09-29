<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Operations\PortalDelete;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalUpdate;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;

describe('PortalSql', function () {
    beforeEach(function () {
        $adapter = new PortalAdapter([
            'driver' => 'Pdo_Mysql',
            'database' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'hostname' => 'localhost',
            'prefix' => 'smf_',
        ]);

        $this->sql = new PortalSql($adapter);
    });

    it('returns PortalSelect from select method without table', function () {
        $select = $this->sql->select();

        expect($select)->toBeInstanceOf(PortalSelect::class);
    });

    it('returns PortalSelect from select method with table', function () {
        $select = $this->sql->select('lp_pages');

        expect($select)->toBeInstanceOf(PortalSelect::class)
            ->and($select->getRawState()['table'])->toBe('smf_lp_pages');
    });

    it('returns PortalInsert from insert method without table', function () {
        $insert = $this->sql->insert();

        expect($insert)->toBeInstanceOf(PortalInsert::class);
    });

    it('returns PortalInsert from insert method with table', function () {
        $insert = $this->sql->insert('lp_blocks');

        expect($insert)->toBeInstanceOf(PortalInsert::class)
            ->and($insert->getRawState()['table'])->toBe('smf_lp_blocks');
    });

    it('returns PortalUpdate from update method without table', function () {
        $update = $this->sql->update();

        expect($update)->toBeInstanceOf(PortalUpdate::class);
    });

    it('returns PortalUpdate from update method with table', function () {
        $update = $this->sql->update('lp_categories');

        expect($update)->toBeInstanceOf(PortalUpdate::class)
            ->and($update->getRawState()['table'])->toBe('smf_lp_categories');
    });

    it('returns PortalDelete from delete method without table', function () {
        $delete = $this->sql->delete();

        expect($delete)->toBeInstanceOf(PortalDelete::class);
    });

    it('returns PortalDelete from delete method with table', function () {
        $delete = $this->sql->delete('lp_tags');

        expect($delete)->toBeInstanceOf(PortalDelete::class)
            ->and($delete->getRawState()['table'])->toBe('smf_lp_tags');
    });
});

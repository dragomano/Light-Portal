<?php

declare(strict_types=1);

use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use LightPortal\Database\Migrations\PortalTable;

describe('PortalTable', function () {
    beforeEach(function () {
        $this->table = new PortalTable('test_table');
    });

    it('adds auto increment column and primary key', function () {
        $column = new Integer('id', false, null, ['auto_increment' => true]);

        $result = $this->table->addAutoIncrementColumn($column);

        expect($result)->toBeInstanceOf(PortalTable::class);
    });

    it('adds primary key', function () {
        $column = new Integer('id');

        $this->table->addColumn($column);
        $result = $this->table->addPrimaryKey('id');

        expect($result)->toBeInstanceOf(PortalTable::class);
    });

    it('adds unique column with index name', function () {
        $column = new Varchar('slug', 255);

        $result = $this->table->addUniqueColumn($column, 'unique_slug');

        expect($result)->toBeInstanceOf(PortalTable::class);
    });

    it('adds unique column without index name', function () {
        $column = new Varchar('alias', 100);

        $result = $this->table->addUniqueColumn($column);

        expect($result)->toBeInstanceOf(PortalTable::class);
    });

    it('adds unique key with name', function () {
        $column = new Varchar('title', 255);

        $this->table->addColumn($column);
        $result = $this->table->addUniqueKey('title', 'uk_title');

        expect($result)->toBeInstanceOf(PortalTable::class);
    });

    it('adds unique key without name', function () {
        $column = new Integer('category_id');

        $this->table->addColumn($column);
        $result = $this->table->addUniqueKey('category_id');

        expect($result)->toBeInstanceOf(PortalTable::class);
    });
});

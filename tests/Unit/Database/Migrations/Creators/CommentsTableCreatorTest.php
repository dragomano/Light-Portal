<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\CommentsTableCreator;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('CommentsTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new CommentsTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(CommentsTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProtectedProperty('tableName');

        expect($result)->toBe('lp_comments');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callProtectedMethod('getFullTableName');

        expect($result)->toBe('lp_comments');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('id')
            ->and($sql)->toContain('parent_id')
            ->and($sql)->toContain('page_id')
            ->and($sql)->toContain('author_id')
            ->and($sql)->toContain('created_at');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_comments'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_comments');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});

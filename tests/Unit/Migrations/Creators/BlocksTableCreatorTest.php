<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Migrations\Creators\BlocksTableCreator;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Expression;

describe('BlocksTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter->shouldReceive('getPlatform')->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new BlocksTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(BlocksTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(BlocksTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_blocks');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(BlocksTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_blocks');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_blocks (
                    block_id INT AUTO_INCREMENT PRIMARY KEY,
                    icon VARCHAR(60) NULL,
                    type VARCHAR(30) NOT NULL,
                    placement VARCHAR(10) NOT NULL,
                    priority TINYINT NOT NULL,
                    permissions TINYINT NOT NULL,
                    status TINYINT NOT NULL DEFAULT 1,
                    areas VARCHAR(255) NOT NULL DEFAULT \'all\',
                    title_class VARCHAR(255) NULL,
                    content_class VARCHAR(255) NULL
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('block_id')
            ->and($result)->toContain('icon')
            ->and($result)->toContain('type')
            ->and($result)->toContain('placement')
            ->and($result)->toContain('priority')
            ->and($result)->toContain('permissions')
            ->and($result)->toContain('status')
            ->and($result)->toContain('areas')
            ->and($result)->toContain('title_class')
            ->and($result)->toContain('content_class');
    });

    it('inserts default data for left-to-right layout', function () {
        Utils::$context['right_to_left'] = false;

        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with(['block_id' => 1])->andReturnSelf();
        $select->shouldReceive('columns')->with(['count' => new Expression('COUNT(*)')], false)->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_blocks')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 0]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $insert = Mockery::mock(PortalInsert::class);
        $insert->shouldReceive('columns')->with(['block_id', 'icon', 'type', 'placement', 'permissions', 'title_class', 'content_class'])->andReturnSelf();
        $insert->shouldReceive('values')->with([1, 'fas fa-user', 'user_info', 'right', 3, TitleClass::first(), ContentClass::first()])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_blocks')->andReturn($insert);
        $insertStatement = Mockery::mock();
        $insertStatement->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);

        $this->creator->insertDefaultData();
    });

    it('inserts default data for right-to-left layout', function () {
        Utils::$context['right_to_left'] = true;

        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with(['block_id' => 1])->andReturnSelf();
        $select->shouldReceive('columns')->with(['count' => new Expression('COUNT(*)')], false)->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_blocks')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 0]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $insert = Mockery::mock(PortalInsert::class);
        $insert->shouldReceive('columns')->with(['block_id', 'icon', 'type', 'placement', 'permissions', 'title_class', 'content_class'])->andReturnSelf();
        $insert->shouldReceive('values')->with([1, 'fas fa-user', 'user_info', 'left', 3, TitleClass::first(), ContentClass::first()])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_blocks')->andReturn($insert);
        $insertStatement = Mockery::mock();
        $insertStatement->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);

        $this->creator->insertDefaultData();
    });

    it('does not insert default data if exists', function () {
        Utils::$context['right_to_left'] = false;

        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with(['block_id' => 1])->andReturnSelf();
        $select->shouldReceive('columns')->with(['count' => new Expression('COUNT(*)')], false)->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_blocks')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 1]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});

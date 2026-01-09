<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\StatementContainerInterface;
use Laminas\Db\Sql\PreparableSqlInterface;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalReplace;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalResult;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalTransactionInterface;
use Tests\ReflectionAccessor;

describe('PortalSql', function () {
    beforeEach(function () {
        $platform = mock(PlatformInterface::class);
        $platform->shouldReceive('getName')->andReturn('SQLite');

        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter->shouldReceive('getPlatform')->andReturn($platform);
        $this->adapter->shouldReceive('getTitle')->andReturn('SQLite');

        $driver = mock(DriverInterface::class);
        $driver->shouldReceive('getDatabasePlatformName')->andReturn('SQLite');
        $this->adapter->shouldReceive('getDriver')->andReturn($driver);

        $this->sql = new PortalSql($this->adapter);
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

    it('returns PortalInsert from insert method without table', function () {
        $insert = $this->sql->insert();

        expect($insert)->toBeInstanceOf(PortalInsert::class);
    });

    it('returns PortalInsert from insert method with table', function () {
        $insert = $this->sql->insert('lp_blocks');

        expect($insert)->toBeInstanceOf(PortalInsert::class)
            ->and($insert->getRawState()['table'])->toBe('smf_lp_blocks');
    });

    it('returns PortalReplace from replace method without table', function () {
        $replace = $this->sql->replace();

        expect($replace)->toBeInstanceOf(PortalReplace::class);
    });

    it('returns PortalReplace from replace method with table', function () {
        $replace = $this->sql->replace('lp_params');

        expect($replace)->toBeInstanceOf(PortalReplace::class)
            ->and($replace->getRawState()['table'])->toBe('smf_lp_params');
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

    it('returns PortalUpdate from update method without table', function () {
        $update = $this->sql->update();

        expect($update)->toBeInstanceOf(PortalUpdate::class);
    });

    it('returns PortalUpdate from update method with table', function () {
        $update = $this->sql->update('lp_categories');

        expect($update)->toBeInstanceOf(PortalUpdate::class)
            ->and($update->getRawState()['table'])->toBe('smf_lp_categories');
    });

    it('returns prefix from getPrefix method', function () {
        expect($this->sql->getPrefix())->toBe('smf_');
    });

    it('returns adapter from getAdapter method', function () {
        expect($this->sql->getAdapter())->toBeInstanceOf(PortalAdapterInterface::class);
    });

    it('returns transaction from getTransaction method', function () {
        $this->sql->getAdapter()->getDriver()
            ->shouldReceive('getConnection')
            ->andReturn(mock(ConnectionInterface::class));

        $transaction = $this->sql->getTransaction();

        expect($transaction)->toBeInstanceOf(PortalTransactionInterface::class);
    });

    it('checks if table exists', function () {
        $result = mock(ResultInterface::class);
        $result->shouldReceive('current')->andReturn(['1' => 1], null);

        $this->adapter->shouldReceive('query')->andReturn($result);

        $accessor = new ReflectionAccessor($this->sql);
        $accessor->setProperty('adapter', $this->adapter);

        expect($this->sql->tableExists('lp_blocks'))->toBeTrue()
            ->and($this->sql->tableExists('lp_nonexistent'))->toBeFalse();
    });

    it('checks if column exists', function () {
        $result = mock(ResultInterface::class);
        $result->shouldReceive('execute')->andReturn($result);

        $this->sql->getAdapter()
            ->shouldReceive('query')
            ->with("PRAGMA table_info(smf_lp_blocks)", Adapter::QUERY_MODE_EXECUTE)
            ->andReturn($result);

        // Mock the iterator behavior
        $iterator = new ArrayIterator([
            ['name' => 'block_id'],
            ['name' => 'title'],
            ['name' => 'content']
        ]);

        $result->shouldReceive('current')->andReturnUsing(function () use ($iterator) {
            return $iterator->current();
        });
        $result->shouldReceive('valid')->andReturnUsing(function () use ($iterator) {
            return $iterator->valid();
        });
        $result->shouldReceive('next')->andReturnUsing(function () use ($iterator) {
            $iterator->next();
        });
        $result->shouldReceive('rewind')->andReturnUsing(function () use ($iterator) {
            $iterator->rewind();
        });

        expect($this->sql->columnExists('lp_blocks', 'title'))->toBeTrue()
            ->and($this->sql->columnExists('lp_blocks', 'nonexistent'))->toBeFalse();
    });

    it('executes queries', function () {
        $sqlObject = mock(PreparableSqlInterface::class);
        $result = mock(ResultInterface::class);

        $statementContainer = mock(StatementContainerInterface::class);
        $statementContainer->shouldReceive('execute')->andReturn($result);

        $statement = mock(StatementInterface::class);
        $statement->shouldReceive('prepare')->with($sqlObject)->andReturn($statementContainer);
        $statement->shouldReceive('execute')->andReturn($result);

        $this->sql->getAdapter()->getDriver()
            ->shouldReceive('createStatement')
            ->andReturn($statement);

        $sqlObject
            ->shouldReceive('prepareStatement')
            ->with($this->sql->getAdapter(), Mockery::type(StatementInterface::class))
            ->andReturn($statementContainer);


        expect($this->sql->execute($sqlObject))->toEqual(new PortalResult($result, $this->sql->getAdapter()));
    });

    it('executes batch insert queries', function () {
        $insert = mock(PortalInsert::class);
        $insert->shouldReceive('isBatch')->andReturn(true);
        $result = mock(ResultInterface::class);

        $insert->shouldReceive('executeBatch')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($insert))->toEqual(new PortalResult($result, $this->sql->getAdapter()));
    });

    it('executes batch replace queries', function () {
        $replace = mock(PortalReplace::class);
        $replace->shouldReceive('isBatch')->andReturn(true);
        $result = mock(ResultInterface::class);

        $replace->shouldReceive('executeBatchReplace')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($replace))->toEqual(new PortalResult($result, $this->sql->getAdapter()));
    });

    it('executes single replace queries', function () {
        $replace = mock(PortalReplace::class);
        $replace->shouldReceive('isBatch')->andReturn(false);
        $result = mock(ResultInterface::class);

        $replace->shouldReceive('executeReplace')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($replace))->toEqual(new PortalResult($result, $this->sql->getAdapter()));
    });
});

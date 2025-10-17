<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\Operations\PortalDelete;
use Bugo\LightPortal\Database\Operations\PortalInsert;
use Bugo\LightPortal\Database\Operations\PortalReplace;
use Bugo\LightPortal\Database\Operations\PortalSelect;
use Bugo\LightPortal\Database\Operations\PortalUpdate;
use Bugo\LightPortal\Database\PortalAdapterInterface;
use Bugo\LightPortal\Database\PortalSql;
use Bugo\LightPortal\Database\PortalTransactionInterface;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\StatementContainerInterface;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\PreparableSqlInterface;

describe('PortalSql', function () {
    beforeEach(function () {
        $platform = mock(PlatformInterface::class);
        $platform->shouldReceive('getName')->andReturn('MySQL');

        $adapter = mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $adapter->shouldReceive('getPlatform')->andReturn($platform);
        $adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $driver = mock(DriverInterface::class);
        $driver->shouldReceive('getDatabasePlatformName')->andReturn('MySQL');
        $adapter->shouldReceive('getDriver')->andReturn($driver);

        $this->sql = new PortalSql($adapter);
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
        $metadataFactory = mock('alias:' . MetadataFactory::class);
        $metadataFactory->shouldReceive('createSourceFromAdapter')
            ->andReturn(mock([
                'getTableNames' => ['smf_lp_blocks', 'smf_lp_pages']
            ]));

        expect($this->sql->tableExists('lp_blocks'))->toBeTrue()
            ->and($this->sql->tableExists('lp_nonexistent'))->toBeFalse();
    });

    it('checks if column exists', function () {
        $metadataFactory = mock('alias:' . MetadataFactory::class);
        $metadataFactory->shouldReceive('createSourceFromAdapter')
            ->andReturn(mock([
                'getColumnNames' => ['block_id', 'title', 'content']
            ]));

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


        expect($this->sql->execute($sqlObject))->toBe($result);
    });

    it('executes batch insert queries', function () {
        $insert = mock(PortalInsert::class);
        $insert->shouldReceive('isBatch')->andReturn(true);
        $result = mock(ResultInterface::class);

        $insert->shouldReceive('executeBatch')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($insert))->toBe($result);
    });

    it('executes batch replace queries', function () {
        $replace = mock(PortalReplace::class);
        $replace->shouldReceive('isBatch')->andReturn(true);
        $result = mock(ResultInterface::class);

        $replace->shouldReceive('executeBatchReplace')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($replace))->toBe($result);
    });

    it('executes single replace queries', function () {
        $replace = mock(PortalReplace::class);
        $replace->shouldReceive('isBatch')->andReturn(false);
        $result = mock(ResultInterface::class);

        $replace->shouldReceive('executeReplace')
            ->with($this->sql->getAdapter())
            ->andReturn($result);

        expect($this->sql->execute($replace))->toBe($result);
    });
});

<?php

declare(strict_types=1);

use Laminas\Db\Extra\Result\ExtendedResultInterface;
use Laminas\Db\Extra\Sql\Operations\ExtendedInsert;
use Laminas\Db\Extra\Sql\Operations\ExtendedSelect;
use Laminas\Db\Extra\Sql\Operations\ExtendedUpdate;
use LightPortal\Database\Migrations\Upgraders\TableUpgrader;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;

describe('TableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getDriver')->andReturn(mock());
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $this->sql = mock(PortalSql::class);
        $this->sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $this->upgrader = new class($this->sql) extends TableUpgrader {
            protected string $tableName = 'old_table';

            public function upgrade(): void {}
        };
    });

    it('migrates rows to translations using update when record exists', function () {
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $rows = [
            ['page_id' => 1, 'content' => 'Test content 1', 'description' => 'Test description 1'],
        ];

        $resultMock = mock(ExtendedResultInterface::class);
        $resultMock->shouldReceive('rewind');
        $resultMock->shouldReceive('valid')->andReturn(true, false);
        $resultMock->shouldReceive('current')->andReturn($rows[0]);
        $resultMock->shouldReceive('next')->once();

        $selectMock = mock(ExtendedSelect::class);
        $selectMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($selectMock);

        $selectResultMock = mock(ExtendedResultInterface::class);
        $selectResultMock->shouldReceive('count')->andReturn(1);

        $this->sql->shouldReceive('execute')->with($selectMock)->andReturn($selectResultMock);

        $updateMock = mock(ExtendedUpdate::class);
        $updateMock
            ->shouldReceive('set')
            ->with(['content' => 'Test content 1', 'description' => 'Test description 1'])
            ->andReturnSelf();
        $updateMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('update')->with('lp_translations')->andReturn($updateMock);
        $this->sql->shouldReceive('execute')->with($updateMock);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callMethod('migrateRowsToTranslations', ['page_id', 'page', $resultMock]);

        expect(true)->toBeTrue();
    });

    it('migrates rows to translations using insert when record does not exist', function () {
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $rows = [
            ['page_id' => 1, 'title' => '', 'content' => 'Test content 1', 'description' => 'Test description 1'],
        ];

        $resultMock = mock(ExtendedResultInterface::class);
        $resultMock->shouldReceive('rewind');
        $resultMock->shouldReceive('valid')->andReturn(true, false);
        $resultMock->shouldReceive('current')->andReturn($rows[0]);
        $resultMock->shouldReceive('next')->once();

        $selectMock = mock(ExtendedSelect::class);
        $selectMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($selectMock);

        $selectResultMock = mock(ExtendedResultInterface::class);
        $selectResultMock->shouldReceive('count')->andReturn(0);

        $this->sql->shouldReceive('execute')->with($selectMock)->andReturn($selectResultMock);

        $insertMock = mock(ExtendedInsert::class);
        $insertMock->shouldReceive('values')->with([
            'item_id'     => 1,
            'type'        => 'page',
            'lang'        => 'english',
            'title'       => '',
            'content'     => 'Test content 1',
            'description' => 'Test description 1',
        ])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_translations')->andReturn($insertMock);
        $this->sql->shouldReceive('execute')->with($insertMock);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callMethod('migrateRowsToTranslations', ['page_id', 'page', $resultMock]);

        expect(true)->toBeTrue();
    });
});

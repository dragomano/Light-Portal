<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Database\PortalTransactionInterface;
use LightPortal\DataHandlers\Traits\HasTransactions;
use LightPortal\Utils\ErrorHandlerInterface;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

beforeEach(function () {
    $this->sqlMock = Mockery::mock(PortalSqlInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $this->testClass = new class {
        use HasTransactions;

        public string $entity = 'test_entity';

        public mixed $sql;

        public mixed $errorHandler;

        public function __construct($sql = null, $errorHandler = null)
        {
            $this->sql = $sql;
            $this->errorHandler = $errorHandler;
        }

        public function cache(): MockInterface|LegacyMockInterface|null
        {
            return Mockery::mock()->shouldReceive('flush')->once()->getMock();
        }

        public function testStartTransaction(array $items): void
        {
            $this->startTransaction($items);
        }

        public function testFinishTransaction(array $results): void
        {
            $this->finishTransaction($results);
        }
    };
});

describe('startTransaction', function () {
    it('begins transaction and sets context', function () {
        $transactionMock = Mockery::mock(PortalTransactionInterface::class);
        $transactionMock->shouldReceive('begin')->withNoArgs()->once();

        $this->sqlMock
            ->shouldReceive('getTransaction')
            ->withNoArgs()
            ->andReturn($transactionMock)
            ->once();

        $this->testClass = new (get_class($this->testClass))($this->sqlMock);

        Utils::$context = [];

        $items = [['id' => 1], ['id' => 2]];

        $this->testClass->testStartTransaction($items);

        expect(Utils::$context['import_successful'])->toBe(2);
    });
});

describe('finishTransaction', function () {
    it('commits when results exist', function () {
        $transactionMock = Mockery::mock(PortalTransactionInterface::class);
        $transactionMock->shouldReceive('begin')->withNoArgs()->once();

        $commitTransactionMock = Mockery::mock(PortalTransactionInterface::class);
        $commitTransactionMock->shouldReceive('commit')->withNoArgs()->once();

        $this->sqlMock
            ->shouldReceive('getTransaction')
            ->withNoArgs()
            ->andReturn($transactionMock, $commitTransactionMock)
            ->twice();

        $this->testClass = new (get_class($this->testClass))($this->sqlMock);

        Utils::$context = ['import_successful' => 2];

        Lang::$txt = [
            'lp_import_success' => 'Import successful: ',
            'lp_test_entity_set' => '{test_entity, plural, one {# test entity} other {# test entities}}'
        ];

        $this->testClass->testStartTransaction([['id' => 1], ['id' => 2]]);
        $this->testClass->testFinishTransaction([1, 2]);

        expect(Utils::$context['import_successful'])->toContain('Import successful');
    });

    it('rolls back when no results', function () {
        $connectionMock = Mockery::mock(ConnectionInterface::class);
        $rollbackTransactionMock = Mockery::mock(PortalTransactionInterface::class);
        $rollbackTransactionMock->shouldReceive('rollback')->withNoArgs()->andReturn($connectionMock)->once();

        $this->sqlMock
            ->shouldReceive('getTransaction')
            ->withNoArgs()
            ->andReturn($rollbackTransactionMock)
            ->once();

        $this->errorHandlerMock
            ->shouldReceive('fatal')
            ->with('lp_import_failed', false)
            ->once()
            ->andThrow(new Exception('lp_import_failed'));

        $testClass = new (get_class($this->testClass))($this->sqlMock, $this->errorHandlerMock);

        Utils::$context = ['import_successful' => 0];

        $testClass->testFinishTransaction([]);
    })->throws(Exception::class);

    it('flushes cache', function () {
        $transactionMock = Mockery::mock(PortalTransactionInterface::class);
        $transactionMock->shouldReceive('begin')->withNoArgs()->once();

        $commitTransactionMock = Mockery::mock(PortalTransactionInterface::class);
        $commitTransactionMock->shouldReceive('commit')->withNoArgs()->once();

        $this->sqlMock
            ->shouldReceive('getTransaction')
            ->withNoArgs()
            ->andReturn($transactionMock, $commitTransactionMock)
            ->twice();

        $testClass = new (get_class($this->testClass))($this->sqlMock);

        Utils::$context = ['import_successful' => 1];

        Lang::$txt = [
            'lp_import_success' => 'Import successful: ',
            'lp_test_entity_set' => '{test_entity, plural, one {# test entity} other {# test entities}}'
        ];

        $testClass->testStartTransaction([['id' => 1]]);
        $testClass->testFinishTransaction([1]);
    });
});

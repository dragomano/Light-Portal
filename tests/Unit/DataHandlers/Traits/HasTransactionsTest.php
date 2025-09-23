<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Traits\HasTransactions;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

beforeEach(function () {
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $this->testClass = new class {
        use HasTransactions;

        public string $entity = 'test_entity';

        public mixed $db;

        public mixed $errorHandler;

        public function __construct($db = null, $errorHandler = null)
        {
            $this->db = $db;
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

it('startTransaction begins transaction and sets context', function () {
    // Mock Db
    $this->dbMock
        ->shouldReceive('transaction')
        ->withNoArgs()
        ->andReturn(true)
        ->once();

    $this->testClass = new (get_class($this->testClass))($this->dbMock);

    // Mock Utils
    Utils::$context = [];

    $items = [['id' => 1], ['id' => 2]];

    $this->testClass->testStartTransaction($items);

    expect(Utils::$context['import_successful'])->toBe(2);
});

it('finishTransaction commits when results exist', function () {
    // Mock Db
    $this->dbMock->shouldReceive('transaction')->withNoArgs()->once();
    $this->dbMock->shouldReceive('transaction')->with('commit')->once();

    $this->testClass = new (get_class($this->testClass))($this->dbMock);

    // Mock Utils
    Utils::$context = ['import_successful' => 2];

    // Mock Lang
    Lang::$txt = [
        'lp_import_success' => 'Import successful: ',
        'lp_test_entity_set' => '{test_entity, plural, one {# test entity} other {# test entities}}'
    ];

    $this->testClass->testStartTransaction([['id' => 1], ['id' => 2]]); // Sets context
    $this->testClass->testFinishTransaction([1, 2]);

    expect(Utils::$context['import_successful'])->toContain('Import successful');
});

it('finishTransaction rolls back when no results', function () {
    // Mock Db
    $this->dbMock->shouldReceive('transaction')->with('rollback')->once();

    // Mock Error Handler
    $this->errorHandlerMock
        ->shouldReceive('fatal')
        ->with('lp_import_failed', false)
        ->once()
        ->andThrow(new Exception('lp_import_failed'));

    $testClass = new (get_class($this->testClass))($this->dbMock, $this->errorHandlerMock);

    // Set import_successful to 0 to trigger rollback
    Utils::$context = ['import_successful' => 0];

    $testClass->testFinishTransaction([]);
})->throws(Exception::class);

it('finishTransaction flushes cache', function () {
    // Mock Db
    $this->dbMock->shouldReceive('transaction')->withNoArgs()->once();
    $this->dbMock->shouldReceive('transaction')->with('commit')->once();

    $testClass = new (get_class($this->testClass))($this->dbMock);

    // Mock Utils
    Utils::$context = ['import_successful' => 1];

    // Mock Lang
    Lang::$txt = [
        'lp_import_success' => 'Import successful: ',
        'lp_test_entity_set' => '{test_entity, plural, one {# test entity} other {# test entities}}'
    ];

    $testClass->testStartTransaction([['id' => 1]]); // Sets context
    $testClass->testFinishTransaction([1]);
});

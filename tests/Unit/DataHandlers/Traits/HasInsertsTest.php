<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\HasInserts;

beforeEach(function () {
    $this->testClass = new class {
        use HasInserts;

        public mixed $sql;

        public function __construct($sql = null)
        {
            $this->sql = $sql;
        }

        public function testInsertData(
            string $table,
            array $data,
            array $keys,
            bool $replace = false,
            int $chunkSize = 100
        ): array
        {
            return $this->insertData($table, $data, $keys, $replace, $chunkSize);
        }
    };
});

it('returns empty array when data is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->testInsertData('test_table', [], []);

    expect($result)->toBe([]);
});

it('returns empty array when columns is empty', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('insert')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 1]])
        ->andReturnSelf();
    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(0);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(null);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $result = $this->testClass->testInsertData('test_table', [['id' => 1]], []);

    expect($result)->toBe([]);
});

it('returns empty array when keys is empty', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('insert')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 1]])
        ->andReturnSelf();
    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(0);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(null);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $result = $this->testClass->testInsertData('test_table', [['id' => 1]], []);

    expect($result)->toBe([]);
});

it('chunks data and calls Db::insert correctly', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('insert')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 1, 'name' => 'Test 1']])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 2, 'name' => 'Test 2']])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 3, 'name' => 'Test 3']])
        ->andReturnSelf();

    $resultMock1 = Mockery::mock();
    $resultMock1->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock1->shouldReceive('getGeneratedValue')->andReturn(1);

    $resultMock2 = Mockery::mock();
    $resultMock2->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock2->shouldReceive('getGeneratedValue')->andReturn(2);

    $resultMock3 = Mockery::mock();
    $resultMock3->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock3->shouldReceive('getGeneratedValue')->andReturn(3);

    $sqlMock->shouldReceive('execute')
        ->andReturn($resultMock1, $resultMock2, $resultMock3);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $data = [
        ['id' => 1, 'name' => 'Test 1'],
        ['id' => 2, 'name' => 'Test 2'],
        ['id' => 3, 'name' => 'Test 3'],
    ];

    $keys = ['id'];

    $result = $this->testClass->testInsertData('test_table', $data, $keys, false, 1); // chunkSize = 1

    expect($result)->toBe([1, 2, 3]);
});

it('uses default chunk size of 100', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('insert')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->andReturnSelf()
        ->times(2);

    $resultMock1 = Mockery::mock();
    $resultMock1->shouldReceive('getAffectedRows')->andReturn(75);
    $resultMock1->shouldReceive('getGeneratedValue')->andReturn(1);

    $resultMock2 = Mockery::mock();
    $resultMock2->shouldReceive('getAffectedRows')->andReturn(75);
    $resultMock2->shouldReceive('getGeneratedValue')->andReturn(76);

    $sqlMock->shouldReceive('execute')
        ->andReturn($resultMock1, $resultMock2);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $data = array_fill(0, 150, ['id' => 1, 'name' => 'Test']); // More than default chunk size

    $result = $this->testClass->testInsertData('test_table', $data, ['id']);

    expect(array_slice($result, 0, 6))->toBe([1, 2, 3, 4, 5, 6]);
});

it('merges results from multiple chunks', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['id'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 1]])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with([['id' => 2]])
        ->andReturnSelf();

    $resultMock1 = Mockery::mock();
    $resultMock1->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock1->shouldReceive('getGeneratedValue')->andReturn(1);

    $resultMock2 = Mockery::mock();
    $resultMock2->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock2->shouldReceive('getGeneratedValue')->andReturn(2);

    $sqlMock->shouldReceive('execute')
        ->andReturn($resultMock1, $resultMock2);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $data = [
        ['id' => 1],
        ['id' => 2],
    ];

    $result = $this->testClass->testInsertData('test_table', $data, ['id'], true, 1);

    expect($result)->toBe([1, 2]);
});

it('handles insert method correctly', function () {
    $data = [['id' => 1]];

    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('insert')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with($data)
        ->andReturnSelf();
    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(1);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);
    $result = $this->testClass->testInsertData('test_table', $data, ['id'], false, 1);
    expect($result)->toBe([1]);
});

it('handles replace method correctly', function () {
    $data = [['id' => 1]];

    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('test_table')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['id'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->with($data)
        ->andReturnSelf();
    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(1);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);
    $result = $this->testClass->testInsertData('test_table', $data, ['id'], true, 1);
    expect($result)->toBe([1]);
});

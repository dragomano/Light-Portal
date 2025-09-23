<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\CanInsertDataTrait;

beforeEach(function () {
    $this->testClass = new class {
        use CanInsertDataTrait;

        public mixed $db;

        public function __construct($db = null)
        {
            $this->db = $db;
        }

        public function testInsertData(
            string $table,
            string $method,
            array $data,
            array $columns,
            array $keys,
            int $chunkSize = 100
        ): array {
            return $this->insertData($table, $method, $data, $columns, $keys, $chunkSize);
        }
    };
});

it('returns empty array when data is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->testInsertData('test_table', 'insert', [], [], []);

    expect($result)->toBe([]);
});

it('returns empty array when columns is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->testInsertData('test_table', 'insert', [['id' => 1]], [], []);

    expect($result)->toBe([]);
});

it('returns empty array when keys is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->testInsertData('test_table', 'insert', [['id' => 1]], ['id' => 'int'], []);

    expect($result)->toBe([]);
});

it('chunks data and calls Db::insert correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('insert', '{db_prefix}test_table', Mockery::any(), [['id' => 1, 'name' => 'Test 1']], Mockery::any(), 2)
        ->once()
        ->andReturn([1]);
    $dbMock->shouldReceive('insert')
        ->with('insert', '{db_prefix}test_table', Mockery::any(), [['id' => 2, 'name' => 'Test 2']], Mockery::any(), 2)
        ->once()
        ->andReturn([2]);
    $dbMock->shouldReceive('insert')
        ->with('insert', '{db_prefix}test_table', Mockery::any(), [['id' => 3, 'name' => 'Test 3']], Mockery::any(), 2)
        ->once()
        ->andReturn([3]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $data = [
        ['id' => 1, 'name' => 'Test 1'],
        ['id' => 2, 'name' => 'Test 2'],
        ['id' => 3, 'name' => 'Test 3'],
    ];

    $columns = [
        'id' => 'int',
        'name' => 'string',
    ];

    $keys = ['id'];

    $result = $this->testClass->testInsertData('test_table', 'insert', $data, $columns, $keys, 1); // chunkSize = 1

    expect($result)->toBe([1, 2, 3]);
});

it('uses default chunk size of 100', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->twice() // Should be called twice for 150 items with chunk size 100
        ->andReturn([1, 2, 3], [4, 5, 6]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $data = array_fill(0, 150, ['id' => 1, 'name' => 'Test']); // More than default chunk size

    $result = $this->testClass->testInsertData('test_table', 'insert', $data, ['id' => 'int', 'name' => 'string'], ['id']);

    expect($result)->toBe([1, 2, 3, 4, 5, 6]);
});

it('merges results from multiple chunks', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}test_table', Mockery::any(), [['id' => 1]], Mockery::any(), 2)
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}test_table', Mockery::any(), [['id' => 2]], Mockery::any(), 2)
        ->once()
        ->andReturn([2]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $data = [
        ['id' => 1],
        ['id' => 2],
    ];

    $result = $this->testClass->testInsertData('test_table', 'replace', $data, ['id' => 'int'], ['id'], 1);

    expect($result)->toBe([1, 2]);
});

it('handles different method types', function () {
    $data = [['id' => 1]];

    // Test insert method
    $dbMock1 = Mockery::mock();
    $dbMock1->shouldReceive('insert')
        ->with('insert', '{db_prefix}test_table', Mockery::any(), $data, Mockery::any(), 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock1);
    $result1 = $this->testClass->testInsertData('test_table', 'insert', $data, ['id' => 'int'], ['id'], 1);
    expect($result1)->toBe([1]);

    // Test replace method
    $dbMock2 = Mockery::mock();
    $dbMock2->shouldReceive('insert')
        ->with('replace', '{db_prefix}test_table', Mockery::any(), $data, Mockery::any(), 2)
        ->once()
        ->andReturn([2]);

    $this->testClass = new (get_class($this->testClass))($dbMock2);
    $result2 = $this->testClass->testInsertData('test_table', 'replace', $data, ['id' => 'int'], ['id'], 1);
    expect($result2)->toBe([2]);
});

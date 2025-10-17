<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\HasInserts;
use Bugo\LightPortal\DataHandlers\Traits\HasParams;

beforeEach(function () {
    $this->testClass = new class {
        use HasParams;
        use HasInserts;

        public mixed $sql;

        public function __construct($sql = null)
        {
            $this->sql = $sql;
        }

        public function callReplaceParams(array $params, array $results): array
        {
            return $this->replaceParams($params, $results);
        }
    };
});

it('does nothing when params or results are empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    // Empty params
    $results = [1, 2];
    $this->testClass->callReplaceParams([], $results);

    // Empty results
    $results = [];
    $this->testClass->callReplaceParams([['item_id' => '1']], $results);

    expect(true)->toBeTrue();
});

it('processes params correctly', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('lp_params')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['item_id', 'type', 'name'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->andReturnSelf()
        ->byDefault();

    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(2);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(10);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $params = [
        [
            'item_id' => '1',
            'type' => 'test_type',
            'name' => 'param1',
            'value' => 'value1',
        ],
        [
            'item_id' => '1',
            'type' => 'test_type',
            'name' => 'param2',
            'value' => 'value2',
        ]
    ];

    $results = [10, 11];

    $result = $this->testClass->callReplaceParams($params, $results);

    expect($result)->toBe([10, 11]);
});

it('handles single param correctly', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('lp_params')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['item_id', 'type', 'name'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->andReturnSelf()
        ->byDefault();

    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(1);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $params = [
        [
            'item_id' => '5',
            'type' => 'block',
            'name' => 'display_type',
            'value' => 'list',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceParams($params, $results);

    expect($result)->toBe([1]);
});

it('handles multiple items with different params', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('lp_params')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['item_id', 'type', 'name'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->andReturnSelf()
        ->byDefault();

    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(3);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(1);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $params = [
        [
            'item_id' => '1',
            'type' => 'block',
            'name' => 'show_title',
            'value' => '1',
        ],
        [
            'item_id' => '1',
            'type' => 'block',
            'name' => 'cache_time',
            'value' => '300',
        ],
        [
            'item_id' => '2',
            'type' => 'block',
            'name' => 'show_title',
            'value' => '0',
        ]
    ];

    $results = [1, 2, 3];

    $result = $this->testClass->callReplaceParams($params, $results);

    expect($result)->toBe([1, 2, 3]);
});

it('uses correct table name', function () {
    $sqlMock = Mockery::mock();
    $sqlMock->shouldReceive('replace')
        ->with('lp_params')
        ->andReturnSelf();
    $sqlMock->shouldReceive('setConflictKeys')
        ->with(['item_id', 'type', 'name'])
        ->andReturnSelf();
    $sqlMock->shouldReceive('batch')
        ->andReturnSelf()
        ->byDefault();

    $resultMock = Mockery::mock();
    $resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $resultMock->shouldReceive('getGeneratedValue')->andReturn(1);

    $sqlMock->shouldReceive('execute')->andReturn($resultMock);

    $this->testClass = new (get_class($this->testClass))($sqlMock);

    $params = [
        [
            'item_id' => '1',
            'type' => 'test_type',
            'name' => 'test',
            'value' => 'value',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceParams($params, $results);

    expect($result)->toBe([1]);
});

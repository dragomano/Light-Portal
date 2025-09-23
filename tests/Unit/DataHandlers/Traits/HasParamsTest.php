<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\CanInsertDataTrait;
use Bugo\LightPortal\DataHandlers\Traits\HasParams;

beforeEach(function () {
    $this->testClass = new class {
        use HasParams;
        use CanInsertDataTrait;

        public mixed $db;

        public function __construct($db = null)
        {
            $this->db = $db;
        }

        public function callReplaceParams(array $params, array $results): void
        {
            $this->replaceParams($params, $results);
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
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_params', Mockery::any(), Mockery::any(), ['item_id', 'type', 'name'], 2)
        ->once()
        ->andReturn([10, 11]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

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

    $this->testClass->callReplaceParams($params, $results);
});

it('handles single param correctly', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_params', [
            'item_id' => 'int',
            'type' => 'string',
            'name' => 'string',
            'value' => 'string',
        ], Mockery::any(), ['item_id', 'type', 'name'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $params = [
        [
            'item_id' => '5',
            'type' => 'block',
            'name' => 'display_type',
            'value' => 'list',
        ]
    ];

    $results = [1];

    $this->testClass->callReplaceParams($params, $results);
});

it('handles multiple items with different params', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_params', [
            'item_id' => 'int',
            'type' => 'string',
            'name' => 'string',
            'value' => 'string',
        ], Mockery::any(), ['item_id', 'type', 'name'], 2)
        ->once()
        ->andReturn([1, 2, 3]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

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

    $this->testClass->callReplaceParams($params, $results);
});

it('uses correct table name', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_params', [
            'item_id' => 'int',
            'type' => 'string',
            'name' => 'string',
            'value' => 'string',
        ], Mockery::any(), ['item_id', 'type', 'name'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $params = [
        [
            'item_id' => '1',
            'type' => 'test_type',
            'name' => 'test',
            'value' => 'value',
        ]
    ];

    $results = [1];

    $this->testClass->callReplaceParams($params, $results);
});

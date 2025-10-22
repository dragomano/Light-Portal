<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasInserts;
use LightPortal\DataHandlers\Traits\HasParams;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::PARAMS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->testClass = new class($this->sql) {
        use HasParams;
        use HasInserts;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
        {
            $this->sql = $sql;
        }

        public function callReplaceParams(array $params = [], bool $replace = true): array
        {
            return $this->replaceParams($params, $replace);
        }
    };
});

it('handles empty params array', function () {
    $result = $this->testClass->callReplaceParams();

    expect($result)->toBe([]);
});

dataset('add params', [
    'one record' => [
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ]
        ],
        1,
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ],
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'show_title',
                'value'   => '1',
            ]
        ],
        2,
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ],
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'show_title',
                'value'   => '1',
            ]
        ]
    ]
]);

it('adds param records', function (array $params, int $expectedCount, array $checks) {
    // Ensure table is empty before adding
    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_params')->execute();
    $count = $rows->current()['count'];
    expect($count)->toBe(0);

    $result = $this->testClass->callReplaceParams($params);

    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($checks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_params WHERE item_id = ? AND type = ? AND name = ?',
                [$check['item_id'], $check['type'], $check['name']]
            );
        $data = $rows->current();

        expect($data['value'])->toBe($check['value']);
    }
})->with('add params');

dataset('replace params', [
    'one record' => [
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'grid',
            ]
        ],
        1,
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'grid',
            ]
        ],
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'grid',
            ],
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'cache_time',
                'value'   => '600',
            ]
        ],
        2,
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'grid',
            ],
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'cache_time',
                'value'   => '600',
            ]
        ],
        [
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'display_type',
                'value'   => 'list',
            ],
            [
                'item_id' => '1',
                'type'    => 'block',
                'name'    => 'show_title',
                'value'   => '1',
            ]
        ]
    ]
]);

it('replaces param records', function (
    array $params,
    int $expectedCount,
    array $checks,
    array $initialParams
) {
    $this->testClass->callReplaceParams($initialParams);

    // Verify initial params are in the database
    foreach ($initialParams as $initial) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_params WHERE item_id = ? AND type = ? AND name = ?',
                [$initial['item_id'], $initial['type'], $initial['name']]
            );
        $data = $rows->current();

        expect($data['value'])->toBe($initial['value']);
    }

    $result = $this->testClass->callReplaceParams($params);

    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($checks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_params WHERE item_id = ? AND type = ? AND name = ?',
                [$check['item_id'], $check['type'], $check['name']]
            );
        $data = $rows->current();

        expect($data['value'])->toBe($check['value']);
    }
})->with('replace params');

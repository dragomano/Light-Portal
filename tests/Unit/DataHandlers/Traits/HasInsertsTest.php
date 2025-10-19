<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasInserts;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::MEMBERS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->testClass = new class($this->sql) {
        use HasInserts;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
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
    $result = $this->testClass->testInsertData('members', [], []);

    expect($result)->toBe([]);
});

dataset('insert data', [
    'one record' => [
        [
            ['real_name' => 'Test User', 'member_name' => 'test_user']
        ],
        ['id_member'],
        false,
        1,
        1,
        [
            [
                'real_name' => 'Test User',
                'member_name' => 'test_user'
            ]
        ]
    ],
    'multiple records' => [
        [
            ['real_name' => 'Test User 1', 'member_name' => 'test_user1'],
            ['real_name' => 'Test User 2', 'member_name' => 'test_user2']
        ],
        ['id_member'],
        false,
        1,
        2,
        [
            [
                'real_name' => 'Test User 1',
                'member_name' => 'test_user1'
            ],
            [
                'real_name' => 'Test User 2',
                'member_name' => 'test_user2'
            ]
        ]
    ]
]);

it('handles insert method correctly', function (
    array $data,
    array $keys,
    bool $replace,
    int $chunkSize,
    int $expectedCount,
    array $checks
) {
    $result = $this->testClass->testInsertData('members', $data, $keys, $replace, $chunkSize);
    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($result as $id) {
        expect($id)->toBeInt()->toBeGreaterThan(0);
    }

    // Verify data was inserted
    foreach ($checks as $index => $check) {
        $id = $result[$index];
        $rows = $this->sql->getAdapter()
            ->query(/** @lang text */ 'SELECT * FROM members WHERE id_member = ?', [$id]);
        $row = $rows->current();

        expect($row['real_name'])->toBe($check['real_name'])
            ->and($row['member_name'])->toBe($check['member_name']);
    }
})->with('insert data');

dataset('replace data', [
    'one record' => [
        [
            ['real_name' => 'Test User', 'member_name' => 'test_user']
        ],
        ['id_member'],
        true,
        1,
        1,
        [
            [
                'real_name' => 'Test User',
                'member_name' => 'test_user'
            ]
        ]
    ],
    'multiple records' => [
        [
            ['real_name' => 'Test User 1', 'member_name' => 'test_user1'],
            ['real_name' => 'Test User 2', 'member_name' => 'test_user2']
        ],
        ['id_member'],
        true,
        1,
        2,
        [
            [
                'real_name' => 'Test User 1',
                'member_name' => 'test_user1'
            ],
            [
                'real_name' => 'Test User 2',
                'member_name' => 'test_user2'
            ]
        ]
    ]
]);

it('handles replace method correctly', function (
    array $data,
    array $keys,
    bool $replace,
    int $chunkSize,
    int $expectedCount,
    array $checks
) {
    $result = $this->testClass->testInsertData('members', $data, $keys, $replace, $chunkSize);
    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($result as $id) {
        expect($id)->toBeInt()->toBeGreaterThan(0);
    }

    // Verify data was inserted
    foreach ($checks as $index => $check) {
        $id = $result[$index];
        $rows = $this->sql->getAdapter()
            ->query(/** @lang text */ 'SELECT * FROM members WHERE id_member = ?', [$id]);
        $row = $rows->current();

        expect($row['real_name'])->toBe($check['real_name'])
            ->and($row['member_name'])->toBe($check['member_name']);
    }
})->with('replace data');

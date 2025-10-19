<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasInserts;
use LightPortal\DataHandlers\Traits\HasTranslations;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->testClass = new class($this->sql) {
        use HasTranslations;
        use HasInserts;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
        {
            $this->sql = $sql;
        }

        public function callReplaceTranslations(array $translations, bool $replace = true): array
        {
            return $this->replaceTranslations($translations, $replace);
        }
    };
});

it('handles empty translations array', function () {
    $result = $this->testClass->callReplaceTranslations([]);

    expect($result)->toBe([]);
});

it('handles NULL values in translation fields', function () {
    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_entity',
            'lang'        => 'english',
            'title'       => null,
            'content'     => null,
            'description' => null,
        ]
    ];

    $result = $this->testClass->callReplaceTranslations($translations);

    expect($result)->toBe([1]);

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND lang = ?', ['1', 'english']);
    $data = $rows->current();

    expect($data['title'])->toBeNull()
        ->and($data['content'])->toBeNull()
        ->and($data['description'])->toBeNull();
});

it('handles empty string values in translation fields', function () {
    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_entity',
            'lang'        => 'english',
            'title'       => '',
            'content'     => '',
            'description' => '',
        ]
    ];

    $result = $this->testClass->callReplaceTranslations($translations);

    expect($result)->toBe([1]);

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND lang = ?', ['1', 'english']);
    $data = $rows->current();

    expect($data['title'])->toBeNull()
        ->and($data['content'])->toBeNull()
        ->and($data['description'])->toBeNull();
});

dataset('add translations', [
    'one record' => [
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'New Title',
                'content'     => 'New Content',
                'description' => 'New Description',
            ]
        ],
        1,
        [
            [
                'item_id'     => '1',
                'lang'        => 'english',
                'title'       => 'New Title',
                'content'     => 'New Content',
                'description' => 'New Description',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'New Title 1',
                'content'     => 'New Content 1',
                'description' => 'New Description 1',
            ],
            [
                'item_id'     => '2',
                'type'        => 'test_entity',
                'lang'        => 'russian',
                'title'       => 'Новый Заголовок 2',
                'content'     => 'Новое Содержимое 2',
                'description' => 'Новое Описание 2',
            ]
        ],
        2,
        [
            [
                'item_id'     => '1',
                'lang'        => 'english',
                'title'       => 'New Title 1',
                'content'     => 'New Content 1',
                'description' => 'New Description 1',
            ],
            [
                'item_id'     => '2',
                'lang'        => 'russian',
                'title'       => 'Новый Заголовок 2',
                'content'     => 'Новое Содержимое 2',
                'description' => 'Новое Описание 2',
            ]
        ]
    ]
]);

it('adds translation records', function (array $translations, int $expectedCount, array $checks) {
    // Ensure table is empty before adding
    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_translations')->execute();
    $count = $rows->current()['count'];
    expect($count)->toBe(0);

    $result = $this->testClass->callReplaceTranslations($translations);

    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($checks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND lang = ?',
                [$check['item_id'], $check['lang']]
            );
        $data = $rows->current();

        expect($data['title'])->toBe($check['title'])
            ->and($data['content'])->toBe($check['content'])
            ->and($data['description'])->toBe($check['description']);
    }
})->with('add translations');

dataset('replace translations', [
    'one record' => [
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'Updated Title',
                'content'     => 'Updated Content',
                'description' => 'Updated Description',
            ]
        ],
        1,
        [
            [
                'item_id'     => '1',
                'lang'        => 'english',
                'title'       => 'Updated Title',
                'content'     => 'Updated Content',
                'description' => 'Updated Description',
            ]
        ],
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'Original Title',
                'content'     => 'Original Content',
                'description' => 'Original Description',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'Updated Title 1',
                'content'     => 'Updated Content 1',
                'description' => 'Updated Description 1',
            ],
            [
                'item_id'     => '2',
                'type'        => 'test_entity',
                'lang'        => 'russian',
                'title'       => 'Обновленный Заголовок 2',
                'content'     => 'Обновленное Содержимое 2',
                'description' => 'Обновленное Описание 2',
            ]
        ],
        2,
        [
            [
                'item_id'     => '1',
                'lang'        => 'english',
                'title'       => 'Updated Title 1',
                'content'     => 'Updated Content 1',
                'description' => 'Updated Description 1',
            ],
            [
                'item_id'     => '2',
                'lang'        => 'russian',
                'title'       => 'Обновленный Заголовок 2',
                'content'     => 'Обновленное Содержимое 2',
                'description' => 'Обновленное Описание 2',
            ]
        ],
        [
            [
                'item_id'     => '1',
                'type'        => 'test_entity',
                'lang'        => 'english',
                'title'       => 'Original Title 1',
                'content'     => 'Original Content 1',
                'description' => 'Original Description 1',
            ],
            [
                'item_id'     => '2',
                'type'        => 'test_entity',
                'lang'        => 'russian',
                'title'       => 'Оригинальный Заголовок 2',
                'content'     => 'Оригинальное Содержимое 2',
                'description' => 'Оригинальное Описание 2',
            ]
        ]
    ]
]);

it('replaces translation records', function (
    array $translations,
    int $expectedCount,
    array $checks,
    array $initialTranslations
) {
    $this->testClass->callReplaceTranslations($initialTranslations);

    // Verify initial translations are in the database
    foreach ($initialTranslations as $initial) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND lang = ?',
                [$initial['item_id'], $initial['lang']]
            );
        $data = $rows->current();

        expect($data['title'])->toBe($initial['title'])
            ->and($data['content'])->toBe($initial['content'])
            ->and($data['description'])->toBe($initial['description']);
    }

    $result = $this->testClass->callReplaceTranslations($translations);

    expect($result)->toBeArray()->toHaveCount($expectedCount);

    foreach ($checks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND lang = ?',
                [$check['item_id'], $check['lang']]
            );
        $data = $rows->current();

        expect($data['title'])->toBe($check['title'])
            ->and($data['content'])->toBe($check['content'])
            ->and($data['description'])->toBe($check['description']);
    }
})->with('replace translations');

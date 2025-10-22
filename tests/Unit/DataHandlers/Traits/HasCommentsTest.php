<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasComments;
use LightPortal\DataHandlers\Traits\HasInserts;
use LightPortal\DataHandlers\Traits\HasTranslations;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->testClass = new class($this->sql) {
        use HasComments;
        use HasInserts;
        use HasTranslations;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
        {
            $this->sql = $sql;
        }

        public function callReplaceComments(array $comments = [], bool $replace = true): array
        {
            return $this->replaceComments($comments, $replace);
        }

        public function callReplaceCommentTranslations(array $translations = []): array
        {
            return $this->replaceCommentTranslations($translations);
        }
    };
});

it('handles empty comments array', function () {
    $result = $this->testClass->callReplaceComments();

    expect($result)->toBe([]);
});

dataset('add comments', [
    'one record' => [
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Test comment',
            ]
        ],
        1,
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
            ]
        ],
        [
            [
                'item_id' => 1,
                'lang'    => 'english',
                'content' => 'Test comment',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ],
            [
                'id'         => 2,
                'page_id'    => 1,
                'author_id'  => 2,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'First comment',
            ],
            [
                'item_id' => 2,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Second comment',
            ]
        ],
        2,
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
            ],
            [
                'id'         => 2,
                'page_id'    => 1,
                'author_id'  => 2,
            ]
        ],
        [
            [
                'item_id' => 1,
                'lang'    => 'english',
                'content' => 'First comment',
            ],
            [
                'item_id' => 2,
                'lang'    => 'english',
                'content' => 'Second comment',
            ]
        ]
    ]
]);

it('adds comment records', function (
    array $comments,
    array $translations,
    int $expectedCount,
    array $commentChecks,
    array $translationChecks
) {
    // Ensure table is empty before adding
    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_comments')->execute();
    $count = $rows->current()['count'];
    expect($count)->toBe(0);

    $result = $this->testClass->callReplaceComments($comments);
    $translationResult = $this->testClass->callReplaceCommentTranslations($translations);

    expect($result)->toBeArray()->toHaveCount($expectedCount)
        ->and($translationResult)->toBeArray()->toHaveCount($expectedCount);

    foreach ($commentChecks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_comments WHERE id = ?',
                [$check['id']]
            );
        $data = $rows->current();

        expect($data['id'])->toBe($check['id'])
            ->and($data['page_id'])->toBe($check['page_id'])
            ->and($data['author_id'])->toBe($check['author_id']);
    }

    foreach ($translationChecks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
                /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND type = ? AND lang = ?',
                [$check['item_id'], 'comment', $check['lang']]
            );
        $data = $rows->current();

        expect($data['content'])->toBe($check['content']);
    }
})->with('add comments');

dataset('replace comments', [
    'one record' => [
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Updated comment',
            ]
        ],
        1,
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'content'    => 'Updated comment',
            ]
        ],
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Original comment',
            ]
        ]
    ],
    'multiple records' => [
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ],
            [
                'id'         => 2,
                'page_id'    => 1,
                'author_id'  => 2,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Updated first comment',
            ],
            [
                'item_id' => 2,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Updated second comment',
            ]
        ],
        2,
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'content'    => 'Updated first comment',
            ],
            [
                'id'         => 2,
                'page_id'    => 1,
                'author_id'  => 2,
                'content'    => 'Updated second comment',
            ]
        ],
        [
            [
                'id'         => 1,
                'page_id'    => 1,
                'author_id'  => 1,
                'created_at' => time(),
            ],
            [
                'id'         => 2,
                'page_id'    => 1,
                'author_id'  => 2,
                'created_at' => time(),
            ]
        ],
        [
            [
                'item_id' => 1,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Original first comment',
            ],
            [
                'item_id' => 2,
                'type'    => 'comment',
                'lang'    => 'english',
                'content' => 'Original second comment',
            ]
        ]
    ]
]);

it('replaces comment records', function (
    array $comments,
    array $translations,
    int $expectedCount,
    array $checks,
    array $initialComments,
    array $initialTranslations
) {
    $this->testClass->callReplaceComments($initialComments);
    $this->testClass->callReplaceCommentTranslations($initialTranslations);

    // Verify initial comments and translations are in the database
    foreach ($initialComments as $initial) {
        $rows = $this->sql->getAdapter()
            ->query(
            /** @lang text */ 'SELECT * FROM lp_comments WHERE id = ?',
                [$initial['id']]
            );
        $data = $rows->current();

        expect($data['id'])->toBe($initial['id'])
            ->and($data['page_id'])->toBe($initial['page_id'])
            ->and($data['author_id'])->toBe($initial['author_id']);
    }

    foreach ($initialTranslations as $initial) {
        $rows = $this->sql->getAdapter()
            ->query(
            /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND type = ? AND lang = ?',
                [$initial['item_id'], 'comment', $initial['lang']]
            );
        $data = $rows->current();

        expect($data['content'])->toBe($initial['content']);
    }

    $result = $this->testClass->callReplaceComments($comments);
    $translationResult = $this->testClass->callReplaceCommentTranslations($translations);

    expect($result)->toBeArray()->toHaveCount($expectedCount)
        ->and($translationResult)->toBeArray()->toHaveCount($expectedCount);

    foreach ($checks as $check) {
        $rows = $this->sql->getAdapter()
            ->query(
            /** @lang text */ 'SELECT * FROM lp_comments WHERE id = ?',
                [$check['id']]
            );
        $data = $rows->current();

        expect($data['id'])->toBe($check['id'])
            ->and($data['page_id'])->toBe($check['page_id'])
            ->and($data['author_id'])->toBe($check['author_id']);

        $translationRows = $this->sql->getAdapter()
            ->query(
            /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND type = ? AND lang = ?',
                [$check['id'], 'comment', 'english']
            );
        $translationData = $translationRows->current();

        expect($translationData['content'])->toBe($check['content']);
    }
})->with('replace comments');

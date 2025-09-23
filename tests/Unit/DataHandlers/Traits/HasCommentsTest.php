<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\CanInsertDataTrait;
use Bugo\LightPortal\DataHandlers\Traits\HasComments;

beforeEach(function () {
    $this->testClass = new class {
        use HasComments;
        use CanInsertDataTrait;

        public mixed $db;

        public function __construct($db = null)
        {
            $this->db = $db;
        }

        public function callReplaceComments(array $comments, array $results): array
        {
            return $this->replaceComments($comments, $results);
        }
    };
});

it('returns empty array when comments array is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->callReplaceComments([], [1, 2, 3]);

    expect($result)->toBe([]);
});

it('returns empty array when results array is empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Test comment',
            'created_at' => time(),
        ]
    ];

    $result = $this->testClass->callReplaceComments($comments, []);

    expect($result)->toBe([]);
});

it('returns empty array when both arrays are empty', function () {
    $this->testClass = new (get_class($this->testClass))();

    $result = $this->testClass->callReplaceComments([], []);

    expect($result)->toBe([]);
});

it('processes single comment correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Test comment',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('processes multiple comments correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1, 2, 3]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'First comment',
            'created_at' => time(),
        ],
        [
            'id' => 2,
            'page_id' => 1,
            'author_id' => 2,
            'message' => 'Second comment',
            'created_at' => time(),
        ],
        [
            'id' => 3,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Third comment',
            'created_at' => time(),
        ]
    ];

    $results = [1, 2, 3];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1, 2, 3]);
});

it('handles comments with parent_id correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1, 2]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'parent_id' => 0,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Parent comment',
            'created_at' => time(),
        ],
        [
            'id' => 2,
            'parent_id' => 1,
            'page_id' => 1,
            'author_id' => 2,
            'message' => 'Reply comment',
            'created_at' => time(),
        ]
    ];

    $results = [1, 2];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1, 2]);
});

it('handles comments with different page_id correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1, 2]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Comment for page 1',
            'created_at' => time(),
        ],
        [
            'id' => 2,
            'page_id' => 2,
            'author_id' => 2,
            'message' => 'Comment for page 2',
            'created_at' => time(),
        ]
    ];

    $results = [1, 2];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1, 2]);
});

it('handles comments with special characters in message', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Comment with special chars: éñünicôdé, 中文, русский, 🚀',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('handles comments with minimum required fields', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Minimal comment',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('handles comments with zero values correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 0,
            'parent_id' => 0,
            'page_id' => 0,
            'author_id' => 0,
            'message' => 'Comment with zeros',
            'created_at' => 0,
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('uses correct table name lp_comments', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', Mockery::any(), Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Test comment',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('uses correct column types for comments', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Test comment',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('uses correct keys for comments', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', Mockery::any(), Mockery::any(), Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => 'Test comment',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('handles empty message correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => '',
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

it('handles very long message correctly', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', [
            'id' => 'int',
            'parent_id' => 'int',
            'page_id' => 'int',
            'author_id' => 'int',
            'message' => 'string',
            'created_at' => 'int',
        ], Mockery::any(), ['id', 'page_id'], 2)
        ->once()
        ->andReturn([1]);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $longMessage = str_repeat('This is a very long comment message. ', 100);

    $comments = [
        [
            'id' => 1,
            'page_id' => 1,
            'author_id' => 1,
            'message' => $longMessage,
            'created_at' => time(),
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceComments($comments, $results);

    expect($result)->toBe([1]);
});

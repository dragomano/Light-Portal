<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\CanInsertDataTrait;
use Bugo\LightPortal\DataHandlers\Traits\HasTranslations;

beforeEach(function () {
    $this->testClass = new class {
        use HasTranslations;
        use CanInsertDataTrait;

        public string $entity = 'test_entity';

        public mixed $db;

        public function __construct($db = null)
        {
            $this->db = $db;
        }

        public function callReplaceTranslations(array $translations, array $results, string $method = 'replace'): array
        {
            return $this->replaceTranslations($translations, $results, $method);
        }
    };
});

it('processes translations with replace method', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        'english' => [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Test Title',
            'content'     => 'Test Content',
            'description' => 'Test Description',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('processes translations with insert method', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('insert', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['id'], 2)
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Test Title',
            'content'     => 'Test Content',
            'description' => 'Test Description',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results, 'insert');

    expect($result)->toBe($results);
});

it('handles database insert errors (insertData returns false)', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn(null);

    // UPDATE query should not be called when insert fails
    $dbMock->shouldNotReceive('query');

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Test Title',
            'content'     => 'Test Content',
            'description' => 'Test Description',
        ]
    ];

    $results = [1];

    // When insertData returns false, the method returns empty array
    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe([]);
});

it('handles NULL values in translation fields', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => null,
            'content'     => null,
            'description' => null,
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('handles numeric array keys', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1, 2]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        0 => [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Title 1',
            'content'     => 'Content 1',
            'description' => 'Description 1',
        ],
        1 => [
            'item_id'     => '2',
            'type'        => 'test_type',
            'lang'        => 'russian',
            'title'       => 'Заголовок 2',
            'content'     => 'Содержимое 2',
            'description' => 'Описание 2',
        ]
    ];

    $results = [1, 2];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('handles very long field values', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $longTitle = str_repeat('A very long title ', 100); // Over 255 chars
    $longDescription = str_repeat('A very long description ', 100); // Over 255 chars
    $longContent = str_repeat('A very long content ', 1000); // Very long content

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => $longTitle,
            'content'     => $longContent,
            'description' => $longDescription,
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});


it('handles database exceptions during SQL operations', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1, 2]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andThrow(new Exception('Database connection error'));

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => '',
            'content'     => '',
            'description' => '',
        ]
    ];

    $results = [1, 2];

    // Should throw exception
    expect(function () use ($translations, $results) {
        $this->testClass->callReplaceTranslations($translations, $results);
    })->toThrow(Exception::class, 'Database connection error');
});

it('updates NULL values in database', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([1, 2]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => '',
            'content'     => '',
            'description' => '',
        ]
    ];

    $results = [1, 2];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('handles complex translation data', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_translations', Mockery::any(), Mockery::any(), ['item_id', 'type', 'lang'], 2)
        ->once()
        ->andReturn([5, 6]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'block',
            'lang'        => 'english',
            'title'       => 'Block Title',
            'content'     => '<p>Block content</p>',
            'description' => 'Block description',
        ],
        [
            'item_id'     => '1',
            'type'        => 'block',
            'lang'        => 'russian',
            'title'       => 'Заголовок блока',
            'content'     => '<p>Содержимое блока</p>',
            'description' => 'Описание блока',
        ]
    ];

    $results = [5, 6];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('uses correct column definitions for replace method', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with(
            'replace',
            '{db_prefix}lp_translations',
            [
                'item_id'     => 'int',
                'type'        => 'string-30',
                'lang'        => 'string-60',
                'title'       => 'string-255',
                'content'     => 'string',
                'description' => 'string-255',
            ],
            Mockery::any(),
            ['item_id', 'type', 'lang'],
            2
        )
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Title',
            'content'     => 'Content',
            'description' => 'Description',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe($results);
});

it('uses correct column definitions for insert method', function () {
    // Mock Db
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('insert')
        ->with(
            'insert',
            '{db_prefix}lp_translations',
            [
                'type'        => 'string-30',
                'lang'        => 'string-60',
                'title'       => 'string-255',
                'content'     => 'string',
                'description' => 'string-255',
                'item_id'     => 'int',
            ],
            Mockery::any(),
            ['id'],
            2
        )
        ->once()
        ->andReturn([1]);

    $dbMock->shouldReceive('query')
        ->with(Mockery::any(), Mockery::any())
        ->once()
        ->andReturn(true);

    $this->testClass = new (get_class($this->testClass))($dbMock);

    $translations = [
        [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Title',
            'content'     => 'Content',
            'description' => 'Description',
        ]
    ];

    $results = [1];

    $result = $this->testClass->callReplaceTranslations($translations, $results, 'insert');

    expect($result)->toBe($results);
});

it('handles empty arrays early return', function () {
    $this->testClass = new (get_class($this->testClass))(null);

    // Test with empty translations array
    $result = $this->testClass->callReplaceTranslations([], [1]);
    expect($result)->toBe([]);

    // Test with empty results array
    $translations = [
        'english' => [
            'item_id'     => '1',
            'type'        => 'test_type',
            'lang'        => 'english',
            'title'       => 'Test Title',
            'content'     => 'Test Content',
            'description' => 'Test Description',
        ]
    ];

    $result = $this->testClass->callReplaceTranslations($translations, []);
    expect($result)->toBe([]);
});

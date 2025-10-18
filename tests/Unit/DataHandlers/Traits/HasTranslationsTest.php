<?php

declare(strict_types=1);

use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalReplace;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalSql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use LightPortal\DataHandlers\Traits\HasInserts;
use LightPortal\DataHandlers\Traits\HasTranslations;

beforeEach(function () {
    $this->sql = Mockery::mock(PortalSql::class)->shouldIgnoreMissing();

    $this->portalReplaceMock = Mockery::mock(PortalReplace::class)->shouldIgnoreMissing();
    $this->portalReplaceMock->shouldReceive('setConflictKeys')->andReturnSelf();
    $this->portalReplaceMock->shouldReceive('batch')->andReturnSelf();

    $this->portalInsertMock = Mockery::mock(PortalInsert::class)->shouldIgnoreMissing();
    $this->portalInsertMock->shouldReceive('setConflictKeys')->andReturnSelf();
    $this->portalInsertMock->shouldReceive('batch')->andReturnSelf();

    $this->resultMock = Mockery::mock(ResultInterface::class);

    $this->updateMock = new PortalUpdate(null, '');

    $this->sql->shouldReceive('replace')->andReturn($this->portalReplaceMock);
    $this->sql->shouldReceive('insert')->andReturn($this->portalInsertMock);
    $this->sql->shouldReceive('update')->andReturn($this->updateMock);
    $this->sql->shouldReceive('query')->andReturn(true);

    $this->testClass = new class ($this->sql) {
        use HasTranslations;
        use HasInserts;

        public string $entity = 'test_entity';

        public mixed $sql;

        public function __construct($sql = null)
        {
            $this->sql = $sql;
        }

        public function callReplaceTranslations(array $translations, array $results, bool $replace = true): array
        {
            return $this->replaceTranslations($translations, $results, $replace);
        }
    };
});

it('processes translations with replace method', function () {
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);

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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);


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

    $result = $this->testClass->callReplaceTranslations($translations, $results, false);

    expect($result)->toBe($results);
});

it('handles database insert errors (insertData returns false)', function () {
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(0);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);

    $this->sql->shouldNotReceive('execute');

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

    $result = $this->testClass->callReplaceTranslations($translations, $results);

    expect($result)->toBe([]);
});

it('handles NULL values in translation fields', function () {
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);


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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(2);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);



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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);



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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->with(Mockery::type('LightPortal\Database\Operations\PortalReplace'))->andReturn($this->resultMock);
    $this->sql->shouldReceive('execute')->with(Mockery::type('LightPortal\Database\Operations\PortalUpdate'))->andThrow(new Exception('Database connection error'));

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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(2);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);



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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(2);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(5);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);



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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);

    $this->portalReplaceMock->shouldReceive('batch')->andReturnSelf();



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
    $this->resultMock->shouldReceive('getAffectedRows')->andReturn(1);
    $this->resultMock->shouldReceive('getGeneratedValue')->andReturn(1);
    $this->sql->shouldReceive('execute')->andReturn($this->resultMock);

    $this->portalInsertMock->shouldReceive('batch')->andReturnSelf();



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

    $result = $this->testClass->callReplaceTranslations($translations, $results, false);

    expect($result)->toBe($results);
});

it('handles empty arrays early return', function () {
    $result = $this->testClass->callReplaceTranslations([], [1]);

    expect($result)->toBe([]);

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

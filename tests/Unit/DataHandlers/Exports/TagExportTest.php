<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Exports\TagExport;
use Bugo\LightPortal\Repositories\TagRepositoryInterface;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\Fixtures;

dataset('tag export scenarios', [
    [true, true],   // hasPages, hasFrequency
    [true, false],
    [false, false],
]);

beforeEach(function () {
    $this->repository = Mockery::mock(TagRepositoryInterface::class);
    $this->requestMock = Mockery::mock(RequestInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->fileMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
});

it('getData processes tag data', function ($hasPages, $hasFrequency) {
    $export = Mockery::mock(TagExport::class, [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock])
        ->makePartial();

    $this->requestMock->shouldReceive('isEmpty')->with('tags')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('tags')->andReturn([1]);

    $export->shouldReceive('request')->andReturn($this->requestMock);

    // Build mock data based on scenario
    $dbResult = [
        [
            'tag_id' => '1',
            'slug'   => 'test-tag',
            'icon'   => 'fas fa-tag',
            'status' => '1',
            'page_id' => $hasPages ? '1' : null,
            'lang'   => 'english',
            'title'  => 'Test Tag'
        ]
    ];

    if ($hasPages) {
        $dbResult[] = [
            'tag_id' => '1',
            'slug'   => 'test-tag',
            'icon'   => 'fas fa-tag',
            'status' => '1',
            'page_id' => '2',
            'lang'   => 'english',
            'title'  => 'Test Tag'
        ];

        if ($hasFrequency) {
            // Add more pages for high frequency scenario
            $dbResult[] = [
                'tag_id' => '1',
                'slug'   => 'test-tag',
                'icon'   => 'fas fa-tag',
                'status' => '1',
                'page_id' => '3',
                'lang'   => 'english',
                'title'  => 'Test Tag'
            ];
            $dbResult[] = [
                'tag_id' => '1',
                'slug'   => 'test-tag',
                'icon'   => 'fas fa-tag',
                'status' => '1',
                'page_id' => '4',
                'lang'   => 'english',
                'title'  => 'Test Tag'
            ];
        }
    }


    $this->dbMock->shouldReceive('query')
        ->once()
        ->andReturn($dbResult);

    $fetchCalls = array_map(fn($row) => $row, $dbResult);
    $fetchCalls[] = [];

    $this->dbMock->shouldReceive('fetchAssoc')
        ->andReturn(...$fetchCalls);

    $this->dbMock->shouldReceive('freeResult')
        ->once();

    $export->shouldAllowMockingProtectedMethods();
    $result = $export->getData();

    expect($result[1]['tag_id'])->toBe('1')
        ->and($result[1]['titles']['english'])->toBe('Test Tag');

    if ($hasPages) {
        expect($result[1]['pages'][1]['id'])->toBe('1');
        if ($hasFrequency) {
            // Simulate multiple pages for frequency
            expect(count($result[1]['pages']))->toBeGreaterThan(1);
        }
    } else {
        expect($result[1])->not->toHaveKey('pages');
    }


    if ($hasFrequency) {
        // Check for frequency field or multiple associations
        expect($result[1])->toHaveKey('pages');
    }
})->with('tag export scenarios');

it('getFile calls createXmlFile with tag attributes', function () {
    $export = Mockery::mock(TagExport::class, [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock])
        ->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    $data = Fixtures::getTagsData();
    $export->shouldReceive('getData')->andReturn($data);
    $export->shouldReceive('createXmlFile')
        ->with($data, ['tag_id', 'status'])
        ->andReturn('/tmp/test.xml');

    $result = $export->getFile();

    expect($result)->toBeString()->not->toBeEmpty();
});

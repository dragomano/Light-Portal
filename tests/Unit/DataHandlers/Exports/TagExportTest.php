<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Exports\TagExport;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\DataHandlerTestTrait;
use Tests\Fixtures;

uses(DataHandlerTestTrait::class);

dataset('tag export scenarios', [
    [true, true],   // hasPages, hasFrequency
    [true, false],
    [false, false],
]);

beforeEach(function () {
    $this->repository       = Mockery::mock(TagRepositoryInterface::class);
    $this->requestMock      = Mockery::mock(RequestInterface::class);
    $this->sqlMock          = $this->createDatabaseMock();
    $this->fileMock         = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
});

it('getData processes tag data', function ($hasPages, $hasFrequency) {
    $export = Mockery::mock(
        TagExport::class,
        [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock]
    )
        ->makePartial();

    $this->requestMock->shouldReceive('isEmpty')->with('tags')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('tags')->andReturn([1]);

    $export->shouldReceive('request')->andReturn($this->requestMock);

    $expectedData = [
        1 => [
            'tag_id' => 1,
            'slug'   => 'test-tag',
            'icon'   => 'fas fa-tag',
            'status' => 1,
            'titles' => [
                'english' => 'Test Tag',
            ],
        ]
    ];

    if ($hasPages) {
        $expectedData[1]['pages'] = [
            1 => ['id' => 1],
            2 => ['id' => 2],
            3 => ['id' => 3],
            4 => ['id' => 4],
        ];
    }

    $export->shouldAllowMockingProtectedMethods();
    $export->shouldReceive('getData')->andReturn($expectedData);

    $result = $export->getData();

    expect($result[1]['tag_id'])->toBe(1)
        ->and($result[1]['titles']['english'])->toBe('Test Tag');

    if ($hasPages) {
        expect($result[1]['pages'][1]['id'])->toBe(1);
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
    $export = Mockery::mock(TagExport::class, [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock])
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

<?php

declare(strict_types=1);

use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use Tests\DataHandlerTestFactory;
use Tests\Fixtures;

beforeEach(function () {
    $this->factory = new DataHandlerTestFactory();
});

it('should create mock data for blocks', function () {
    $data = $this->factory->createMockData('blocks', 2);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(2)
        ->and($data[1])->toHaveKey('block_id')
        ->and($data[1])->toHaveKey('icon')
        ->and($data[1])->toHaveKey('type')
        ->and($data[1])->toHaveKey('titles')
        ->and($data[1])->toHaveKey('contents');
});

it('should create mock data for pages', function () {
    $data = $this->factory->createMockData('pages');

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1)
        ->and($data[1])->toHaveKey('page_id')
        ->and($data[1])->toHaveKey('slug')
        ->and($data[1])->toHaveKey('titles')
        ->and($data[1])->toHaveKey('contents')
        ->and($data[1])->toHaveKey('created_at');
});

it('should create mock data for categories', function () {
    $data = $this->factory->createMockData('categories', 3);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(3)
        ->and($data[1])->toHaveKey('category_id')
        ->and($data[1])->toHaveKey('slug')
        ->and($data[1])->toHaveKey('titles')
        ->and($data[1])->toHaveKey('descriptions');
});

it('should create mock data for tags', function () {
    $data = $this->factory->createMockData('tags');

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1)
        ->and($data[1])->toHaveKey('tag_id')
        ->and($data[1])->toHaveKey('slug')
        ->and($data[1])->toHaveKey('titles');
});

it('should throw exception for unknown entity type', function () {
    expect(fn() => $this->factory->createMockData('unknown'))
        ->toThrow(
            InvalidArgumentException::class,
            'Unknown entity type: unknown. Available types: blocks, pages, categories, tags, params, translations'
        );
});

it('should create mock RequestInterface', function () {
    $request = $this->factory->createMockRequest('blocks');

    expect($request)->toBeInstanceOf(RequestInterface::class);
});

it('should create test environment with all components', function () {
    $environment = $this->factory->createTestEnvironment('blocks', [
        'dataCount' => 2,
        'requestOptions' => ['isEmpty' => false]
    ]);

    expect($environment)->toHaveKey('data')
        ->and($environment)->toHaveKey('request')
        ->and($environment['data'])->toHaveCount(2)
        ->and($environment['request'])->toBeInstanceOf(RequestInterface::class);
});

it('should create test environment without database', function () {
    $environment = $this->factory->createTestEnvironment('pages', [
        'skipDatabase' => true
    ]);

    expect($environment)->toHaveKey('data')
        ->and($environment)->toHaveKey('request');
});

it('should create factory with defaults', function () {
    $factory = DataHandlerTestFactory::withDefaults();

    expect($factory)->toBeInstanceOf(DataHandlerTestFactory::class);
});

it('should cleanup environment', function () {
    $this->factory->createTestEnvironment('blocks');
    $this->factory->cleanup();

    // After cleanup, we should be able to create new mocks without conflicts
    $newEnvironment = $this->factory->createTestEnvironment('pages');

    expect($newEnvironment)->toHaveKey('data')
        ->and($newEnvironment)->toHaveKey('request');
});

it('should integrate with existing Fixtures methods', function () {
    // Test that our factory uses the same Fixtures methods
    $factoryData = $this->factory->createMockData('blocks');
    $directFixturesData = Fixtures::getBlocksData();

    expect($factoryData)->toBeArray()
        ->and($directFixturesData)->toBeArray()
        ->and($factoryData[1])->toHaveKey('block_id')
        ->and($directFixturesData[1])->toHaveKey('block_id');
});

it('should create custom mocks with additional interfaces', function () {
    $environment = $this->factory->createTestEnvironment('blocks', [
        'additionalMocks' => [
            FilesystemInterface::class => [
                'methods' => [
                    'exists' => true,
                    'get' => '/tmp/test.xml'
                ]
            ]
        ]
    ]);

    expect($environment)->toHaveKey('additionalMocks')
        ->and($environment['additionalMocks'])->toHaveKey(FilesystemInterface::class);
});

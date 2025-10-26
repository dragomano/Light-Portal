<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Exports\CategoryExport;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use Tests\AppMockRegistry;
use Tests\DataHandlerTestTrait;
use Tests\Fixtures;

use function Pest\Faker\fake;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->repository = mock(CategoryRepositoryInterface::class);
    $this->fileMock = mock(FilesystemInterface::class);
    $this->errorHandlerMock = mock(ErrorHandlerInterface::class);

    $mocks = $this->setupDataHandlerTestEnvironment([
        'databaseOptions' => ['fetchAssocResult' => 'skip'],
        'additionalMocks' => [
            CategoryRepositoryInterface::class => $this->repository,
            FilesystemInterface::class         => $this->fileMock,
            ErrorHandlerInterface::class       => $this->errorHandlerMock,
        ]
    ]);

    $this->requestMock = $mocks['request'];
    $this->sqlMock = $this->createDatabaseMock();
});

afterEach(function () {
    AppMockRegistry::clear();
    Mockery::close();
});

dataset('category export scenarios', function () {
    $scenarios = [];

    $icons = [
        'fas fa-folder', 'fas fa-book', 'fas fa-newspaper', 'fas fa-images',
        'fas fa-video', 'fas fa-music', 'fas fa-gamepad', 'fas fa-graduation-cap',
        null,
    ];

    $priorities = [1, 5, 10];
    $statuses = [0, 1];

    // Generate 10 random scenarios to cover different combinations
    for ($i = 0; $i < 10; $i++) {
        $icon        = fake()->randomElement($icons);
        $description = fake()->optional(0.7)->sentence(); // 70% chance of having description
        $priority    = fake()->randomElement($priorities);
        $status      = fake()->randomElement($statuses);
        $slug        = fake()->unique()->slug(2);

        $scenarios[] = [
            'icon'        => $icon,
            'description' => $description,
            'priority'    => $priority,
            'status'      => $status,
            'slug'        => $slug,
        ];
    }

    return $scenarios;
});

it('getData processes category data correctly', function ($icon, $description, $priority, $status, $slug) {
    $export = mock(CategoryExport::class, [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    $this->requestMock->shouldReceive('isEmpty')->with('categories')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('categories')->andReturn([1]);

    $expectedData = [
        1 => [
            'category_id'  => 1,
            'parent_id'    => 0,
            'slug'         => $slug,
            'icon'         => $icon,
            'priority'     => $priority,
            'status'       => $status,
            'titles'       => ['english' => 'Test Category'],
            'descriptions' => $description ? ['english' => $description] : [],
        ]
    ];

    $export->shouldReceive('getData')->andReturn($expectedData);

    $result = $export->getData();

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1);

    $category = $result[1];

    expect($category['category_id'])->toBe(1)
        ->and($category['slug'])->toBe($slug)
        ->and($category['icon'])->toBe($icon)
        ->and($category['priority'])->toBe($priority)
        ->and($category['status'])->toBe($status);

    // Verify multilingual content handling when description is present
    if ($description !== null) {
        expect($category)->toHaveKey('descriptions')
            ->and($category['descriptions'])->toHaveKey('english')
            ->and($category['descriptions']['english'])->toBe($description);
    }
})->with('category export scenarios');

dataset('category file export counts', function () {
    return [
        [1], // Single category
        [3], // Multiple categories
        [5], // Larger set
    ];
});

it('getFile calls createXmlFile with category attributes', function ($count) {
    $export = mock(CategoryExport::class, [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock])
        ->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    $data = Fixtures::getCategoriesData($count);
    $export->shouldReceive('getData')->andReturn($data);

    $export->shouldReceive('createXmlFile')
        ->with($data, ['category_id', 'priority', 'status'])
        ->andReturn('/tmp/test.xml');

    $result = $export->getFile();

    expect($result)->toBeString()->not->toBeEmpty();
})->with('category file export counts');

dataset('multilingual test languages', function () {
    return [
        ['english'],
        ['russian'],
        ['german'],
        ['french'],
        ['spanish'],
    ];
});

it('should generate multilingual category content with faker', function ($lang) {
    $multilingualContent = Fixtures::getMultilingualContent();

    expect($multilingualContent)->toHaveKey($lang)
        ->and($multilingualContent[$lang])->toHaveKey('title')
        ->and($multilingualContent[$lang])->toHaveKey('content')
        ->and($multilingualContent[$lang])->toHaveKey('description')
        ->and($multilingualContent[$lang])->toHaveKey('welcome_message')
        ->and($multilingualContent[$lang]['title'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['content'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['description'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['welcome_message'])->toBeString()->not->toBeEmpty();
})->with('multilingual test languages');

dataset('category content types', function () {
    return [
        ['general'],
        ['news'],
        ['tutorials'],
        ['gallery'],
    ];
});

it('should generate category-specific content based on type', function ($type) {
    $content = Fixtures::getCategorySpecificContent($type);

    expect($content)->toHaveKey('title')
        ->and($content)->toHaveKey('description')
        ->and($content)->toHaveKey('welcome_text')
        ->and($content)->toHaveKey('icon')
        ->and($content)->toHaveKey('featured_content')
        ->and($content)->toHaveKey('category_guidelines')
        ->and($content['icon'])->toBeString()
        ->and($content['description'])->toBeString()->not->toBeEmpty()
        ->and($content['welcome_text'])->toBeString()->not->toBeEmpty();

    match ($type) {
        'general' => expect($content['icon'])->toBe('fas fa-folder'),
        'news' => expect($content['icon'])->toBe('fas fa-newspaper'),
        'tutorials' => expect($content['icon'])->toBe('fas fa-graduation-cap'),
        'gallery' => expect($content['icon'])->toBe('fas fa-images'),
    };
})->with('category content types');

it('should generate boundary test data for categories', function () {
    $boundaryData = Fixtures::getBoundaryTestData('category');

    expect($boundaryData)->toHaveKey('min_values')
        ->and($boundaryData)->toHaveKey('max_values')
        ->and($boundaryData)->toHaveKey('edge_cases')
        ->and($boundaryData['min_values']['priority'])->toBe(1)
        ->and($boundaryData['min_values']['status'])->toBe(0)
        ->and($boundaryData['max_values']['priority'])->toBe(10)
        ->and($boundaryData['max_values']['status'])->toBe(1)
        ->and($boundaryData['edge_cases'])->toHaveKey('very_long_title')
        ->and($boundaryData['edge_cases'])->toHaveKey('very_long_content')
        ->and($boundaryData['edge_cases'])->toHaveKey('special_chars_title')
        ->and($boundaryData['edge_cases']['very_long_title'])->toBeString()
        ->and($boundaryData['edge_cases']['very_long_content'])->toBeString()
        ->and($boundaryData['edge_cases']['special_chars_title'])->toBeString();
});

it('should generate invalid data for validation testing', function () {
    $invalidData = Fixtures::getInvalidModelData('category');

    expect($invalidData)->toBeArray()
        ->and($invalidData)->toHaveKey('id')
        ->and($invalidData)->toHaveKey('slug')
        ->and($invalidData)->toHaveKey('icon')
        ->and($invalidData)->toHaveKey('priority')
        ->and($invalidData)->toHaveKey('status')
        ->and($invalidData)->toHaveKey('title')
        ->and($invalidData)->toHaveKey('description')
        ->and($invalidData['id'])->toBe('invalid_id')
        ->and($invalidData['slug'])->toBe('')
        ->and($invalidData['icon'])->toBe('')
        ->and($invalidData['priority'])->toBe(-1)
        ->and($invalidData['status'])->toBe(999)
        ->and($invalidData['title'])->toBe('')
        ->and($invalidData['description'])->toBe('');
});

it('should handle database error gracefully during export', function () {
    $export = mock(
        CategoryExport::class,
        [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock]
    )->makePartial();

    $this->requestMock->shouldReceive('isEmpty')->with('categories')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('categories')->andReturn([1]);

    $export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn([]);

    $result = $export->getData();

    expect($result)->toBe([]);
});

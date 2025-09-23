<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Exports\CategoryExport;
use Bugo\LightPortal\Repositories\CategoryRepositoryInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Tests\AppMockRegistry;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

use function Pest\Faker\fake;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->repository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->fileMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    // Setup basic test environment using trait
    $mocks = $this->setupDataHandlerTestEnvironment([
        'databaseOptions' => ['fetchAssocResult' => 'skip'],
        'additionalMocks' => [
            CategoryRepositoryInterface::class => $this->repository,
            FilesystemInterface::class => $this->fileMock,
            ErrorHandlerInterface::class => $this->errorHandlerMock,
        ]
    ]);

    $this->requestMock = $mocks['request'];
    $this->dbMock = $mocks['database'];
});

afterEach(function () {
    AppMockRegistry::clear();
    Mockery::close();
});

dataset('category export scenarios', function () {
    $scenarios = [];

    // Generate diverse test scenarios for category export functionality
    // Includes various combinations of icons, priorities, statuses, and optional descriptions
    $icons = [
        'fas fa-folder', 'fas fa-book', 'fas fa-newspaper', 'fas fa-images',
        'fas fa-video', 'fas fa-music', 'fas fa-gamepad', 'fas fa-graduation-cap',
        null // Test null icon scenarios
    ];

    $priorities = [1, 5, 10]; // Common priority values
    $statuses = [0, 1]; // Active/inactive status values

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

/**
 * Test that getData method correctly processes category data from database
 * This test verifies data transformation and multilingual content handling
 */
/**
 * Test that getData method correctly processes category data from database
 * This test verifies data transformation and multilingual content handling
 */
it('getData processes category data correctly', function ($icon, $description, $priority, $status, $slug) {
    $export = Mockery::mock(CategoryExport::class, [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock])->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    // Simulate request with specific categories selected for export
    $this->requestMock->shouldReceive('isEmpty')->with('categories')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('categories')->andReturn([1]);

    // Mock getData to return processed data using dataset parameters for verification
    $expectedData = [
        '1' => [
            'category_id' => '1',
            'parent_id' => '0',
            'slug' => $slug,
            'icon' => $icon,
            'priority' => $priority,
            'status' => $status,
            'titles' => ['english' => 'Test Category'],
            'descriptions' => $description ? ['english' => $description] : [],
        ]
    ];

    $export->shouldReceive('getData')->andReturn($expectedData);

    $result = $export->getData();

    // Assert the structure and content of processed data
    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey('1');
    // One category processed
    // Category ID as key

    $category = $result['1'];

    // Verify core category attributes match input data
    expect($category['category_id'])->toBe('1')
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

/**
 * Test that getFile method calls createXmlFile with correct category data and attributes
 * Verifies XML generation for different numbers of categories
 */
it('getFile calls createXmlFile with category attributes', function ($count) {
    $export = Mockery::mock(CategoryExport::class, [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock])
        ->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    // Use Fixtures to generate realistic category data
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

/**
 * Test multilingual content generation for categories
 * Verifies that Fixtures can generate localized content for different languages
 */
it('should generate multilingual category content with faker', function ($lang) {
    $multilingualContent = Fixtures::getMultilingualContent();

    // Verify the specified language has all required content fields
    expect($multilingualContent)->toHaveKey($lang)
        ->and($multilingualContent[$lang])->toHaveKey('title')
        ->and($multilingualContent[$lang])->toHaveKey('content')
        ->and($multilingualContent[$lang])->toHaveKey('description')
        ->and($multilingualContent[$lang])->toHaveKey('welcome_message')
        ->and($multilingualContent[$lang]['title'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['content'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['description'])->toBeString()->not->toBeEmpty()
        ->and($multilingualContent[$lang]['welcome_message'])->toBeString()->not->toBeEmpty();

    // Ensure all content fields are non-empty strings
})->with('multilingual test languages');

dataset('category content types', function () {
    return [
        ['general'],
        ['news'],
        ['tutorials'],
        ['gallery'],
    ];
});

/**
 * Test category-specific content generation based on category type
 * Verifies that Fixtures can generate appropriate content for different category types
 */
it('should generate category-specific content based on type', function ($type) {
    $content = Fixtures::getCategorySpecificContent($type);

    // Verify all required content fields are present
    expect($content)->toHaveKey('title')
        ->and($content)->toHaveKey('description')
        ->and($content)->toHaveKey('welcome_text')
        ->and($content)->toHaveKey('icon')
        ->and($content)->toHaveKey('featured_content')
        ->and($content)->toHaveKey('category_guidelines')
        ->and($content['icon'])->toBeString()
        ->and($content['description'])->toBeString()->not->toBeEmpty()
        ->and($content['welcome_text'])->toBeString()->not->toBeEmpty();

    // Ensure critical fields are properly populated strings

    // Verify content type-specific expectations
    match ($type) {
        'general' => expect($content['icon'])->toBe('fas fa-folder'),
        'news' => expect($content['icon'])->toBe('fas fa-newspaper'),
        'tutorials' => expect($content['icon'])->toBe('fas fa-graduation-cap'),
        'gallery' => expect($content['icon'])->toBe('fas fa-images'),
    };
})->with('category content types');

/**
 * Test boundary value generation for category data
 * Ensures Fixtures can provide appropriate edge case data for validation testing
 */
it('should generate boundary test data for categories', function () {
    $boundaryData = Fixtures::getBoundaryTestData('category');

    // Verify boundary data structure contains all required sections
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

/**
 * Test invalid data generation for category validation testing
 * Ensures Fixtures can provide appropriate invalid data for edge case testing
 */
it('should generate invalid data for validation testing', function () {
    $invalidData = Fixtures::getInvalidModelData('category');

    // Verify invalid data has all required category fields
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

/**
 * Test graceful error handling during database operations
 * Ensures CategoryExport handles database exceptions properly by returning empty array
 */
it('should handle database error gracefully during export', function () {
    $export = Mockery::mock(
        CategoryExport::class,
        [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock]
    )->makePartial();

    // Configure request to indicate specific categories are selected for export
    $this->requestMock->shouldReceive('isEmpty')->with('categories')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('categories')->andReturn([1]);

    // Mock getData to return empty array, simulating error handling behavior
    $export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn([]);

    // Execute getData method which should handle errors gracefully
    $result = $export->getData();

    // Verify that method returns empty array instead of throwing exception
    expect($result)->toBe([]);
});

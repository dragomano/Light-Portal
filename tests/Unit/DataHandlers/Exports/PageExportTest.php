<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Exports\PageExport;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

use function Pest\Faker\fake;

// Use the DataHandlerTestTrait for additional test utilities
uses(DataHandlerTestTrait::class);

// Dataset defining various page export scenarios with different content types, statuses, permissions, entry types, and comment options
dataset('page export scenarios', function () {
    $types = [...ContentType::names(), 'markdown'];
    $entityTypes = EntryType::names();
    $permissions = Permission::values();
    $hasCommentsOptions = [true, false];

    $scenarios = [];

    foreach ($types as $i => $type) {
        $scenarios[] = [
            $type,
            $i % 2, // alternate 0/1
            $permissions[$i % count($permissions)],
            $entityTypes[$i % count($entityTypes)],
            $hasCommentsOptions[$i % count($hasCommentsOptions)],
        ];
    }

    return $scenarios;
});

beforeEach(function () {
    $this->repository = Mockery::mock(PageRepositoryInterface::class);
    $this->requestMock = Mockery::mock(RequestInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->filesystemMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $this->export = Mockery::mock(PageExport::class, [
        $this->repository,
        $this->dbMock,
        $this->filesystemMock,
        $this->errorHandlerMock
    ])->makePartial()->shouldAllowMockingProtectedMethods();

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
    Mockery::close();
});

// Test that the PageExport class returns the expected attribute fields for page data export
it('returns correct attribute fields', function () {
    // Retrieve the attribute fields from the export handler
    $attributeFields = $this->export->shouldAllowMockingProtectedMethods()->getAttributeFields();

    // Assert that the fields match the expected list for page attributes
    expect($attributeFields)->toBe([
        'page_id', 'category_id', 'author_id', 'permissions', 'status', 'num_views',
        'num_comments', 'created_at', 'updated_at', 'deleted_at', 'last_comment_id',
    ]);
});

// Test that the PageExport class returns the correct nested field rules for XML structure
it('returns correct nested field rules', function () {
    // Retrieve the nested field rules defining how nested data is structured in XML
    $nestedRules = $this->export->shouldAllowMockingProtectedMethods()->getNestedFieldRules();

    // Verify that all expected nested fields are present
    expect($nestedRules)->toHaveKey('titles')
        ->and($nestedRules)->toHaveKey('params')
        ->and($nestedRules)->toHaveKey('contents')
        ->and($nestedRules)->toHaveKey('descriptions')
        ->and($nestedRules)->toHaveKey('comments')
        // Check titles configuration: simple element without CDATA
        ->and($nestedRules['titles']['type'])->toBe('element')
        ->and($nestedRules['titles']['useCDATA'])->toBeFalse()
        // Check contents configuration: element with CDATA for rich content
        ->and($nestedRules['contents']['type'])->toBe('element')
        ->and($nestedRules['contents']['useCDATA'])->toBeTrue()
        // Check comments configuration: subitem structure with comment elements
        ->and($nestedRules['comments']['type'])->toBe('subitem')
        ->and($nestedRules['comments']['elementName'])->toBe('comment')
        // Verify comments have id field and message uses CDATA
        ->and($nestedRules['comments']['subFields'])->toHaveKey('id')
        ->and($nestedRules['comments']['subFields']['message']['useCDATA'])->toBeTrue();
});

// Test that the PageExport class returns the correct entity name for database table identification
it('returns correct entity name', function () {
    // Get the entity name from the export handler
    $entity = $this->export->getEntity();

    // Assert that it returns 'pages' as the entity identifier
    expect($entity)->toBe('pages');
});

// Test various page export scenarios with different content types, statuses, permissions, entry types, and comment configurations
it('handles various page export scenarios', function (
    string $contentType,
    int $status,
    int $permissions,
    string $entryType,
    bool $hasComments
) {
    // Setup request mock expectations for page selection
    $this->requestMock->shouldReceive('isEmpty')->with('pages')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(false);
    $this->requestMock->shouldReceive('get')->with('pages')->andReturn([1]);

    $dbResultMock = Mockery::mock();
    $this->dbMock->shouldReceive('query')->once()->andReturn($dbResultMock);

    // Generate realistic page data using Fixtures
    $pageData = Fixtures::getPagesData()[1];
    $rows = [];

    // Prepare main page data row with scenario-specific parameters
    $rows[] = [
        'page_id' => 1,
        'category_id' => $pageData['category_id'],
        'author_id' => $pageData['author_id'],
        'slug' => $pageData['slug'],
        'type' => $contentType,
        'entry_type' => $entryType,
        'permissions' => $permissions,
        'status' => $status,
        'num_views' => $pageData['num_views'],
        'num_comments' => (string) ($hasComments ? fake()->numberBetween(1, 20) : 0),
        'created_at' => $pageData['created_at'],
        'updated_at' => $pageData['updated_at'],
        'deleted_at' => '0',
        'last_comment_id' => (string) ($hasComments ? fake()->numberBetween(1, 10) : 0),
        'lang' => 'english',
        'title' => $pageData['titles']['english'],
        'content' => Fixtures::getRealisticBlockContent($contentType), // Use Fixtures for realistic content
        'description' => $pageData['descriptions']['english'],
        'name' => null,
        'value' => null,
        'id' => null,
        'parent_id' => null,
        'com_author_id' => null,
        'message' => null,
        'com_created_at' => null,
    ];

    // Add comment rows if comments are enabled and page has them
    if ($hasComments && ! empty($pageData['comments'])) {
        foreach ($pageData['comments'] as $comment) {
            $rows[] = [
                'page_id' => 1,
                'category_id' => $pageData['category_id'],
                'author_id' => $pageData['author_id'],
                'slug' => $pageData['slug'],
                'type' => $contentType,
                'entry_type' => $entryType,
                'permissions' => $permissions,
                'status' => $status,
                'num_views' => $pageData['num_views'],
                'num_comments' => (string) fake()->numberBetween(1, 20),
                'created_at' => $pageData['created_at'],
                'updated_at' => $pageData['updated_at'],
                'deleted_at' => '0',
                'last_comment_id' => (string) fake()->numberBetween(1, 10),
                'lang' => null,
                'title' => null,
                'content' => null,
                'description' => null,
                'name' => null,
                'value' => null,
                'id' => $comment['id'],
                'parent_id' => $comment['parent_id'],
                'com_author_id' => $comment['author_id'],
                'message' => $comment['message'],
                'com_created_at' => $comment['created_at'],
            ];
        }
    }

    // Configure database mock to return the prepared rows
    $this->dbMock
        ->shouldReceive('fetchAssoc')
        ->with($dbResultMock)
        ->andReturnValues([...$rows, []]);

    $this->dbMock->shouldReceive('freeResult')->with($dbResultMock)->once();

    $this->export->shouldReceive('request')->andReturn($this->requestMock);

    // Execute the export and get processed data
    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    // Assert basic page structure and scenario parameters
    expect($result)->toHaveKey(1)
        ->and($result[1]['type'])->toBe($contentType)
        ->and($result[1]['status'])->toBe($status)
        ->and($result[1]['entry_type'])->toBe($entryType)
        ->and($result[1]['permissions'])->toBe($permissions);

    // Assert comments structure if comments are expected
    if ($hasComments && ! empty($pageData['comments'])) {
        expect($result[1])->toHaveKey('comments')
            ->and($result[1]['comments'])->toHaveKey(1)
            ->and($result[1]['comments'][1]['message'])->toBe($pageData['comments'][1]['message']);
    } else {
        expect($result[1])->not()->toHaveKey('comments');
    }
})->with('page export scenarios');

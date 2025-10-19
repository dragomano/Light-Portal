<?php

declare(strict_types=1);

use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Exports\PageExport;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\DataHandlerTestTrait;

uses(DataHandlerTestTrait::class);

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
    $this->sqlMock = Mockery::mock(PortalSqlInterface::class);
    $this->filesystemMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $this->export = Mockery::mock(PageExport::class, [
        $this->repository,
        $this->sqlMock,
        $this->filesystemMock,
        $this->errorHandlerMock
    ])->makePartial()->shouldAllowMockingProtectedMethods();

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
    Mockery::close();
});

it('returns correct attribute fields', function () {
    $attributeFields = $this->export->shouldAllowMockingProtectedMethods()->getAttributeFields();

    expect($attributeFields)->toBe([
        'page_id', 'category_id', 'author_id', 'permissions', 'status', 'num_views',
        'num_comments', 'created_at', 'updated_at', 'deleted_at', 'last_comment_id',
    ]);
});

it('returns correct nested field rules', function () {
    $nestedRules = $this->export->shouldAllowMockingProtectedMethods()->getNestedFieldRules();

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
        // Verify comments have id field and messages use CDATA
        ->and($nestedRules['comments']['subFields'])->toHaveKey('id')
        ->and($nestedRules['comments']['subFields']['messages']['useCDATA'])->toBeTrue();
});

it('returns correct entity name', function () {
    $entity = $this->export->getEntity();

    expect($entity)->toBe('pages');
});

it('handles various page export scenarios', function (
    string $contentType,
    int $status,
    int $permissions,
    string $entryType,
    bool $hasComments
) {
    $this->requestMock->shouldReceive('isEmpty')->with('pages')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(false);
    $this->requestMock->shouldReceive('get')->with('pages')->andReturn([1]);

    $expectedData = [
        1 => [
            'page_id'         => 1,
            'category_id'     => 1,
            'author_id'       => 1,
            'slug'            => 'test-page',
            'type'            => $contentType,
            'entry_type'      => $entryType,
            'permissions'     => $permissions,
            'status'          => $status,
            'num_views'       => 0,
            'num_comments'    => $hasComments ? 5 : 0,
            'created_at'      => time(),
            'updated_at'      => 0,
            'deleted_at'      => 0,
            'last_comment_id' => $hasComments ? 1 : 0,
            'titles'          => [
                'english'     => 'Test Page',
            ],
            'contents'        => [
                'english'     => 'Test content',
            ],
            'descriptions'    => [
                'english'     => 'Test description',
            ],
        ]
    ];

    if ($hasComments) {
        $expectedData[1]['comments'] = [
            1 => [
                'id'         => 1,
                'parent_id'  => 0,
                'author_id'  => 1,
                'messages'   => [
                    'english' => 'Maiores neque amet numquam quos non et sed sed. Rerum doloremque non ducimus incidunt dolores delectus.',
                ],
                'created_at' => time(),
            ]
        ];
    }

    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn($expectedData);

    $this->export->shouldReceive('request')->andReturn($this->requestMock);

    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    expect($result)->toHaveKey(1)
        ->and($result[1]['type'])->toBe($contentType)
        ->and($result[1]['status'])->toBe($status)
        ->and($result[1]['entry_type'])->toBe($entryType)
        ->and($result[1]['permissions'])->toBe($permissions);

    if ($hasComments) {
        expect($result[1])->toHaveKey('comments')
            ->and($result[1]['comments'])->toHaveKey(1)
            ->and($result[1]['comments'][1])->toHaveKey('messages')
            ->and($result[1]['comments'][1]['messages'])->toHaveKey('english')
            ->and($result[1]['comments'][1]['messages']['english'])->toBe('Maiores neque amet numquam quos non et sed sed. Rerum doloremque non ducimus incidunt dolores delectus.');
    } else {
        expect($result[1])->not()->toHaveKey('comments');
    }
})->with('page export scenarios');

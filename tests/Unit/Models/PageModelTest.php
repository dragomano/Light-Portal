<?php

declare(strict_types=1);

use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Models\PageModel;

it('initializes with empty data and default values', function () {
    $model = new PageModel();
    $data  = $model->toArray();

    expect($data['id'])->toBe(0)
        ->and($data['category_id'])->toBe(0)
        ->and($data['author_id'])->toBe(0)
        ->and($data['slug'])->toBe('')
        ->and($data['type'])->toBe('')
        ->and($data['entry_type'])->toBe('')
        ->and($data['permissions'])->toBe(0)
        ->and($data['status'])->toBe(0)
        ->and($data['num_views'])->toBe(0)
        ->and($data['created_at'])->toBe(0)
        ->and($data['updated_at'])->toBe(0)
        ->and($data['deleted_at'])->toBe(0)
        ->and($data['last_comment_id'])->toBe(0)
        ->and($data['title'])->toBe('')
        ->and($data['content'])->toBe('')
        ->and($data['description'])->toBe('')
        ->and($data['date'])->toBe('')
        ->and($data['time'])->toBe('')
        ->and($data['tags'])->toBe([])
        ->and($data['options'])->toBe([]);
});

it('initializes with custom data', function () {
    $data = [
        'id'           => 123,
        'category_id'  => 45,
        'author_id'    => 67,
        'slug'         => 'test-page',
        'type'         => ContentType::HTML->name(),
        'entry_type'   => EntryType::INTERNAL->name(),
        'permissions'  => Permission::ALL->value,
        'status'       => Status::ACTIVE->value,
        'num_views'    => 100,
        'created_at'   => 1672531200,
        'updated_at'   => 1672617600,
        'deleted_at'   => 0,
        'last_comment_id' => 0,
        'title'        => 'Test Page',
        'content'      => 'Test content',
        'description'  => 'Test description',
        'date'         => '2023-01-01',
        'time'         => '12:00',
        'tags'         => ['tag1', 'tag2'],
        'options'      => ['key' => 'value'],
    ];

    $model  = new PageModel($data);
    $result = $model->toArray();

    expect($result)->toBe($data);
});

it('converts page_id alias to id', function () {
    $data   = ['page_id' => 456];
    $model  = new PageModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(456)
        ->and($result)->not->toHaveKey('page_id');
});

it('overrides id if both page_id and id are provided', function () {
    $data = [
        'id'      => 100,
        'page_id' => 200,
    ];

    $model  = new PageModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(200);
});

it('filters unknown fields', function () {
    $data = [
        'id'              => 1,
        'title'           => 'Test',
        'unknown_field'   => 'should be filtered',
        'another_unknown' => 123,
        'invalid_field'   => true,
        'extra_data'      => ['should', 'be', 'filtered'],
    ];

    $model  = new PageModel($data);
    $result = $model->toArray();

    expect($result)->toHaveKey('id')
        ->and($result)->toHaveKey('title')
        ->and($result)->not->toHaveKey('unknown_field')
        ->and($result)->not->toHaveKey('another_unknown')
        ->and($result)->not->toHaveKey('invalid_field')
        ->and($result)->not->toHaveKey('extra_data');
});

it('handles complex tags and options arrays', function () {
    $data = [
        'tags'     => ['tag1', 'tag2', 'tag3'],
        'options'  => ['show_author' => true],
    ];

    $model  = new PageModel($data);
    $result = $model->toArray();

    expect($result['tags'])->toBe(['tag1', 'tag2', 'tag3'])
        ->and($result['options'])->toBe(['show_author' => true]);
});

<?php

declare(strict_types=1);

use LightPortal\Enums\Status;
use LightPortal\Models\TagModel;

it('initializes with empty data and default values', function () {
    $model = new TagModel();
    $data  = $model->toArray();

    expect($data['id'])->toBe(0)
        ->and($data['slug'])->toBe('')
        ->and($data['icon'])->toBe('')
        ->and($data['status'])->toBe(0)
        ->and($data['title'])->toBe('');
});

it('initializes with custom data', function () {
    $data = [
        'id'    => 123,
        'slug'  => 'test-tag',
        'icon'  => 'fas fa-tag',
        'status'=> Status::ACTIVE->value,
        'title' => 'Test Tag',
    ];

    $model  = new TagModel($data);
    $result = $model->toArray();

    expect($result)->toBe($data);
});

it('converts tag_id alias to id', function () {
    $data   = ['tag_id' => 456];
    $model  = new TagModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(456)
        ->and($result)->not()->toHaveKey('tag_id');
});

it('overrides id if both tag_id and id are provided', function () {
    $data = [
        'id'     => 100,
        'tag_id' => 200,
    ];
    $model  = new TagModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(200);
});

it('filters unknown fields', function () {
    $data = [
        'id'    => 1,
        'title' => 'Test',
        'extra_field' => 'should be filtered',
    ];

    $model  = new TagModel($data);
    $result = $model->toArray();

    expect($result)->not()->toHaveKey('extra_field')
        ->and($result['id'])->toBe(1)
        ->and($result['title'])->toBe('Test');
});

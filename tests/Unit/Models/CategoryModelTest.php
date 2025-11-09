<?php

declare(strict_types=1);

use LightPortal\Enums\Status;
use LightPortal\Models\CategoryModel;

it('initializes with empty data and default values', function () {
    $model = new CategoryModel();
    $data  = $model->toArray();

    expect($data['id'])->toBe(0)
        ->and($data['slug'])->toBe('')
        ->and($data['icon'])->toBe('')
        ->and($data['priority'])->toBe(0)
        ->and($data['status'])->toBe(0)
        ->and($data['title'])->toBe('')
        ->and($data['description'])->toBe('');
});

it('initializes with custom data', function () {
    $data = [
        'id'          => 123,
        'slug'        => 'test-category',
        'icon'        => 'fas fa-folder',
        'priority'    => 5,
        'status'      => Status::ACTIVE->value,
        'title'       => 'Test Category',
        'description' => 'Test description',
    ];

    $model  = new CategoryModel($data);
    $result = $model->toArray();

    expect($result)->toBe($data);
});

it('converts category_id alias to id', function () {
    $data   = ['category_id' => 456];
    $model  = new CategoryModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(456)
        ->and($result)->not->toHaveKey('category_id');
});

it('overrides id if both category_id and id are provided', function () {
    $data = [
        'id'          => 100,
        'category_id' => 200,
    ];
    $model  = new CategoryModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(200);
});

it('filters unknown fields', function () {
    $data = [
        'id'            => 1,
        'title'         => 'Test',
        'unknown_field' => 'should be filtered',
        'another_field' => 'also filtered',
    ];

    $model  = new CategoryModel($data);
    $result = $model->toArray();

    expect($result)->toHaveKey('id')
        ->and($result)->toHaveKey('title')
        ->and($result)->not->toHaveKey('unknown_field')
        ->and($result)->not->toHaveKey('another_field');
});

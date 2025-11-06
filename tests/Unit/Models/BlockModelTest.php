<?php

declare(strict_types=1);

use LightPortal\Enums\Action;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Placement;
use LightPortal\Enums\Status;
use LightPortal\Enums\TitleClass;
use LightPortal\Models\BlockModel;

it('initializes with empty data and default values', function () {
    $model = new BlockModel();
    $data  = $model->toArray();

    expect($data['id'])->toBe(0)
        ->and($data['icon'])->toBe('')
        ->and($data['type'])->toBe('')
        ->and($data['placement'])->toBe('')
        ->and($data['priority'])->toBe(0)
        ->and($data['permissions'])->toBe(0)
        ->and($data['status'])->toBe(0)
        ->and($data['areas'])->toBe('')
        ->and($data['title_class'])->toBe('')
        ->and($data['content_class'])->toBe('')
        ->and($data['title'])->toBe('')
        ->and($data['content'])->toBe('')
        ->and($data['description'])->toBe('')
        ->and($data['options'])->toBe([]);
});

it('initializes with custom data', function () {
    $data = [
        'id'            => 123,
        'icon'          => 'fas fa-star',
        'type'          => ContentType::HTML->name(),
        'placement'     => Placement::RIGHT->name(),
        'priority'      => 5,
        'permissions'   => Permission::ALL->value,
        'status'        => Status::ACTIVE->value,
        'areas'         => Action::ALL->value,
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::INFOBOX->value,
        'title'         => 'Test Block',
        'content'       => 'Test content',
        'description'   => 'Test description',
        'options'       =>  ['key' => 'value'],
    ];

    $model  = new BlockModel($data);
    $result = $model->toArray();

    expect($result)->toBe($data);
});

it('converts block_id to id', function () {
    $data   = ['block_id' => 456];
    $model  = new BlockModel($data);
    $result = $model->toArray();

    expect($result['id'])->toBe(456)
        ->and($result)->not->toHaveKey('block_id');
});

it('overrides id if both block_id and id are provided', function () {
    $data = [
        'id'       => 100,
        'block_id' => 200,
    ];
    $model  = new BlockModel($data);
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
    ];

    $model  = new BlockModel($data);
    $result = $model->toArray();

    expect($result)->toHaveKey('id')
        ->and($result)->toHaveKey('title')
        ->and($result)->not->toHaveKey('unknown_field')
        ->and($result)->not->toHaveKey('another_unknown')
        ->and($result)->not->toHaveKey('invalid_field');
});

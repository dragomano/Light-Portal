<?php

declare(strict_types=1);

use LightPortal\Enums\Status;
use LightPortal\Models\TagFactory;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->factory    = new TagFactory();
    $this->reflection = new ReflectionAccessor($this->factory);
});

it('populates missing fields with default values', function () {
    $result = $this->reflection->callProtectedMethod('populate', [[]]);

    expect($result['status'])->toBe(Status::ACTIVE->value);
});

it('creates tag with custom data and handles bbcode', function () {
    $data = [
        'title' => '[b]Bold Title[/b]',
    ];

    $tag = $this->factory->create($data);

    $tagData = $tag->toArray();
    expect($tagData['title'])->toBe('Bold Title');
});

it('preserves existing values', function () {
    $data = [
        'title'  => 'Test',
        'status' => Status::INACTIVE->value,
        'slug'   => 'test-slug',
    ];

    $result = $this->reflection->callProtectedMethod('populate', [$data]);

    expect($result['status'])->toBe(Status::INACTIVE->value)
        ->and($result['title'])->toBe('Test')
        ->and($result['slug'])->toBe('test-slug');
});

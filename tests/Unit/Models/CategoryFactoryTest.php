<?php

declare(strict_types=1);

use LightPortal\Enums\Status;
use LightPortal\Models\CategoryFactory;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->factory    = new CategoryFactory();
    $this->reflection = new ReflectionAccessor($this->factory);
});

it('populates missing fields with default values', function () {
    $result = $this->reflection->callProtectedMethod('populate', [[]]);

    expect($result['status'])->toBe(Status::ACTIVE->value);
});

it('creates category with custom data and handles bbcode', function () {
    $data = [
        'title'       => '[b]Bold Title[/b]',
        'description' => '[p]Paragraph text[/p]',
    ];

    $category = $this->factory->create($data);

    $categoryData = $category->toArray();
    expect($categoryData['title'])->toBe('Bold Title')
        ->and($categoryData['description'])->toBe('Paragraph text');
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

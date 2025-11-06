<?php

declare(strict_types=1);

use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Models\PageFactory;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->factory    = new PageFactory();
    $this->reflection = new ReflectionAccessor($this->factory);
});

it('populates missing fields with default values', function () {
    $result = $this->reflection->callProtectedMethod('populate', [[]]);

    expect($result['type'])->toBe(ContentType::BBC->name())
        ->and($result['entry_type'])->toBe(EntryType::DEFAULT->name())
        ->and($result['permissions'])->toBe(Permission::MEMBER->value)
        ->and($result['status'])->toBe(Status::UNAPPROVED->value)
        ->and($result['created_at'])->toBeGreaterThan(0)
        ->and($result['date'])->not->toBeEmpty()
        ->and($result['time'])->not->toBeEmpty()
        ->and($result['tags'])->toBe([]);
});

it('creates page with custom data and handles bbcode', function () {
    $data = [
        'title'       => '[b]Bold Title[/b]',
        'description' => '[p]Paragraph text[/p]',
    ];

    $page = $this->factory->create($data);

    $pageData = $page->toArray();
    expect($pageData['title'])->toBe('Bold Title')
        ->and($pageData['description'])->toBe('Paragraph text');
});

it('preserves existing values', function () {
    $data = [
        'title'       => 'Test',
        'type'        => ContentType::HTML->name(),
        'entry_type'  => EntryType::INTERNAL->name(),
        'permissions' => Permission::ADMIN->value,
        'status'      => Status::ACTIVE->value,
    ];

    $result = $this->reflection->callProtectedMethod('populate', [$data]);

    expect($result['type'])->toBe(ContentType::HTML->name())
        ->and($result['entry_type'])->toBe(EntryType::INTERNAL->name())
        ->and($result['permissions'])->toBe(Permission::ADMIN->value)
        ->and($result['status'])->toBe(Status::ACTIVE->value)
        ->and($result['title'])->toBe('Test');
});

it('processes tags as array', function () {
    $data = ['tags' => 'tag1,tag2,tag3'];
    $result = $this->reflection->callProtectedMethod('populate', [$data]);

    expect($result['tags'])->toBe(['tag1', 'tag2', 'tag3']);
});

it('handles tags as array without splitting', function () {
    $data = ['tags' => ['tag1', 'tag2', 'tag3']];
    $result = $this->reflection->callProtectedMethod('populate', [$data]);

    expect($result['tags'])->toBe(['tag1', 'tag2', 'tag3']);
});

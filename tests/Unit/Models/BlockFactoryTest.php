<?php

declare(strict_types=1);

use LightPortal\Enums\Action;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Placement;
use LightPortal\Enums\Status;
use LightPortal\Models\BlockFactory;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->factory    = new BlockFactory();
    $this->reflection = new ReflectionAccessor($this->factory);
});

it('populates missing fields with default values', function () {
    $result = $this->reflection->callMethod('populate', [[]]);

    expect($result['placement'])->toBe(Placement::TOP->name())
        ->and($result['status'])->toBe(Status::ACTIVE->value)
        ->and($result['areas'])->toBe(Action::ALL->value);
});

it('creates block with custom data and handles bbcode', function () {
    $data = [
        'title'       => '[b]Bold Title[/b]',
        'description' => '[p]Paragraph text[/p]',
    ];

    $block = $this->factory->create($data);

    $blockData = $block->toArray();
    expect($blockData['title'])->toBe('Bold Title')
        ->and($blockData['description'])->toBe('Paragraph text');
});

it('preserves existing values', function () {
    $data = [
        'title'       => 'Test',
        'placement'   => Placement::RIGHT->name(),
        'permissions' => Permission::MOD->value,
    ];

    $result = $this->reflection->callMethod('populate', [$data]);

    expect($result['placement'])->toBe(Placement::RIGHT->name())
        ->and($result['permissions'])->toBe(Permission::MOD->value)
        ->and($result['title'])->toBe('Test');
});

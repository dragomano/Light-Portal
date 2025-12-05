<?php

declare(strict_types=1);

use Bugo\Bricks\RendererInterface;
use Bugo\Compat\Utils;
use LightPortal\UI\Tables\TableRenderer;

beforeEach(function () {
    $this->renderer = new TableRenderer();
});

it('implements RendererInterface', function () {
    expect($this->renderer)->toBeInstanceOf(RendererInterface::class);
});

it('works correctly with minimal data', function () {
    $data = ['id' => 'minimal'];

    $this->renderer->render($data);

    expect(Utils::$context['sub_template'])->toBe('show_list')
        ->and(Utils::$context['default_list'])->toBe('minimal');
});

it('creates ItemList object with provided data', function () {
    $data = ['id' => 'test_list', 'title' => 'Test List'];

    $this->renderer->render($data);

    expect(Utils::$context['sub_template'])->toBe('show_list')
        ->and(Utils::$context['default_list'])->toBe('test_list');
});

it('sets sub_template context to show_list', function () {
    $data = ['id' => 'test_id'];

    $this->renderer->render($data);

    expect(Utils::$context['sub_template'])->toBe('show_list');
});

it('sets default_list context from data id', function () {
    $testId = 'custom_table_id';
    $data = ['id' => $testId];

    $this->renderer->render($data);

    expect(Utils::$context['default_list'])->toBe($testId);
});

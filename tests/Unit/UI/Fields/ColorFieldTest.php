<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Fields\ColorField;
use LightPortal\UI\Fields\InputField;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->field = new ColorField('test_field', 'Test Field');
    $this->accessor = new ReflectionAccessor($this->field);
});

it('extends InputField', function () {
    expect($this->field)->toBeInstanceOf(InputField::class);
});

it('initializes with color type', function () {
    expect($this->accessor->getProperty('type'))->toBe('color');
});

it('writes to Utils context with color type', function () {
    $this->field->setValue('#ff0000');

    $this->field->__destruct();

    expect(Utils::$context['posting_fields']['test_field']['label']['text'])->toBe('Test Field')
        ->and(Utils::$context['posting_fields']['test_field']['input']['type'])->toBe('color')
        ->and(Utils::$context['posting_fields']['test_field']['input']['attributes']['value'])->toBe('#ff0000');
});

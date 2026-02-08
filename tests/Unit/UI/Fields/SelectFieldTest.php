<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Fields\AbstractField;
use LightPortal\UI\Fields\SelectField;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->field = new SelectField('test_field', 'Test Field');
    $this->accessor = new ReflectionAccessor($this->field);
});

it('extends AbstractField', function () {
    expect($this->field)->toBeInstanceOf(AbstractField::class);
});

it('initializes with select type', function () {
    expect($this->accessor->getProperty('type'))->toBe('select');
});

it('sets options and returns self', function () {
    $this->field->setValue('opt1');
    $options = ['opt1' => 'Option 1', 'opt2' => 'Option 2'];

    $result = $this->field->setOptions($options);

    expect($result)->toBeInstanceOf(SelectField::class)
        ->and($this->accessor->getProperty('options'))->toBe($options);
});

it('builds select options with selected value', function () {
    $options = ['opt1' => 'Option 1', 'opt2' => 'Option 2'];
    $this->field->setOptions($options)->setValue('opt2');

    $this->field->__destruct();

    $expectedOptions = [
        'Option 1' => ['value' => 'opt1', 'selected' => false],
        'Option 2' => ['value' => 'opt2', 'selected' => true],
    ];

    expect(Utils::$context['posting_fields']['test_field']['input']['options'])
        ->toBe($expectedOptions);
});

it('handles empty options array', function () {
    $this->field->setValue('opt1');
    $this->field->setOptions([]);

    $this->field->__destruct();

    $options = Utils::$context['posting_fields']['test_field']['input']['options'] ?? null;
    expect($options)->toBeNull();
});

it('writes to Utils context with select type', function () {
    $this->field->setOptions(['opt1' => 'Option 1'])->setValue('opt1');

    $this->field->__destruct();

    expect(Utils::$context['posting_fields']['test_field']['label']['text'])->toBe('Test Field')
        ->and(Utils::$context['posting_fields']['test_field']['input']['type'])->toBe('select');
});

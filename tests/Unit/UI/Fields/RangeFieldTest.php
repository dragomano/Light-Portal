<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\RangeField;
use Nette\Utils\Html;

beforeEach(function () {
    $this->field = new RangeField('test_field', 'Test Field');
});

it('extends CustomField', function () {
    $this->field->setValue(0);

    expect($this->field)->toBeInstanceOf(CustomField::class);
});

it('builds HTML structure with div container', function () {
    $this->field->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect($html)->toBeInstanceOf(Html::class)
        ->and((string) $html)->toContain('<div')
        ->and((string) $html)->toContain('</div>');
});

it('creates Alpine.js x-data attribute with field name and value', function () {
    $this->field->setValue(75);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('x-data')
        ->and((string) $html)->toContain('{ \'test_field\' : 75 }');
});

it('creates input element with correct type and attributes', function () {
    $this->field->setValue(30);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('<input')
        ->and((string) $html)->toContain('type="range"')
        ->and((string) $html)->toContain('id="test_field"')
        ->and((string) $html)->toContain('name="test_field"')
        ->and((string) $html)->toContain('x-model="test_field"');
});

it('sets min attribute when provided', function () {
    $this->field->setAttribute('min', 0)->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('min="0"');
});

it('sets max attribute when provided', function () {
    $this->field->setAttribute('max', 100)->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('max="100"');
});

it('sets step attribute when provided', function () {
    $this->field->setAttribute('step', 5)->setValue(25);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('step="5"');
});

it('omits min attribute when not provided', function () {
    $this->field->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->not->toContain('min=');
});

it('omits max attribute when not provided', function () {
    $this->field->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->not->toContain('max=');
});

it('omits step attribute when not provided', function () {
    $this->field->setValue(50);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->not->toContain('step=');
});

it('creates span element for value display', function () {
    $this->field->setValue(60);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('<span')
        ->and((string) $html)->toContain('</span>');
});

it('sets correct classes and style on span', function () {
    $this->field->setValue(40);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('class="progress_bar amt"')
        ->and((string) $html)->toContain('style="margin-left: 10px"');
});

it('binds span to Alpine.js with x-text', function () {
    $this->field->setValue(80);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('x-text="test_field"');
});

it('handles numeric zero value', function () {
    $this->field->setValue(0);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('{ \'test_field\' : 0 }');
});

it('handles float values correctly', function () {
    $this->field->setAttribute('step', 0.1)->setValue(7.5);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('{ \'test_field\' : 7.5 }')
        ->and((string) $html)->toContain('step="0.1"');
});

it('handles all range attributes together', function () {
    $this->field->setAttribute('min', 10)
        ->setAttribute('max', 200)
        ->setAttribute('step', 10)
        ->setValue(100);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect((string) $html)->toContain('min="10"')
        ->and((string) $html)->toContain('max="200"')
        ->and((string) $html)->toContain('step="10"')
        ->and((string) $html)->toContain('{ \'test_field\' : 100 }');
});

it('writes correct label and input configuration to context', function () {
    $this->field->setValue(90);

    $this->field->__destruct();

    $label = Utils::$context['posting_fields']['test_field']['label']['html'];
    expect($label)->toBeInstanceOf(Html::class)
        ->and((string) $label)->toContain('Test Field');
});

it('writes HTML structure to context', function () {
    $this->field->setValue(25);

    $this->field->__destruct();

    $html = Utils::$context['posting_fields']['test_field']['input']['html'];

    expect($html)->toBeInstanceOf(Html::class)
        ->and((string) $html)->toBeString()
        ->and((string) $html)->toContain('div')
        ->and((string) $html)->toContain('input')
        ->and((string) $html)->toContain('span');
});

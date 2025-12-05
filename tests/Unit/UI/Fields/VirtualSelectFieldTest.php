<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Fields\SelectField;
use LightPortal\UI\Fields\VirtualSelectField;

beforeEach(function () {
    $this->field = new VirtualSelectField('test_field', 'Test Field');
});

it('extends SelectField', function () {
    expect($this->field)->toBeInstanceOf(SelectField::class);
});

it('initializes VirtualSelect with correct configuration', function () {
    $javascriptCode = Utils::$context['javascript_inline']['defer'][0] ?? '';

    expect($javascriptCode)->toContain('VirtualSelect.init')
        ->and($javascriptCode)->toContain('ele: "#test_field"')
        ->and($javascriptCode)->toContain('hideClearButton: true')
        ->and($javascriptCode)->toContain('dropboxWrapper: "body"');
});

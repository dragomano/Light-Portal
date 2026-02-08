<?php

declare(strict_types=1);

use LightPortal\UI\Tables\HiddenInput;

describe('HiddenInput', function () {
    it('renders hidden input', function () {
        $html = HiddenInput::make();

        expect($html)->toContain('type="hidden"');
    });
});

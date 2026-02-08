<?php

declare(strict_types=1);

use LightPortal\UI\Tables\Button;

describe('Button', function () {
    it('renders submit input with attributes', function () {
        $html = Button::make('save', 'Save', 'button primary');

        expect($html)
            ->toContain('type="submit"')
            ->toContain('name="save"')
            ->toContain('value="Save"')
            ->toContain('class="button primary"');
    });
});

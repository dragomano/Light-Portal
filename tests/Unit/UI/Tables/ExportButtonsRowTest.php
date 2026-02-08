<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\UI\Tables\ExportButtonsRow;

beforeEach(function () {
    Lang::$txt['lp_export_selection'] = 'Export selection';
    Lang::$txt['lp_export_all'] = 'Export all';
});

describe('ExportButtonsRow', function () {
    it('renders export buttons with hidden input', function () {
        $row = ExportButtonsRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('type="hidden"')
            ->toContain('export_selection')
            ->toContain('Export selection')
            ->toContain('export_all')
            ->toContain('Export all');
    });
});

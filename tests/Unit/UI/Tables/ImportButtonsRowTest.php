<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\UI\Tables\ImportButtonsRow;

beforeEach(function () {
    Lang::$txt['lp_import_selection'] = 'Import selection';
    Lang::$txt['lp_import_all'] = 'Import all';
});

describe('ImportButtonsRow', function () {
    it('renders import buttons with hidden input', function () {
        $row = ImportButtonsRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('type="hidden"')
            ->toContain('import_selection')
            ->toContain('Import selection')
            ->toContain('import_all')
            ->toContain('Import all');
    });
});

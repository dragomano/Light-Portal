<?php

declare(strict_types=1);

use LightPortal\UI\Tables\CheckboxColumn;

describe('CheckboxColumn', function () {
    it('builds header checkbox and row checkbox data', function () {
        $column = CheckboxColumn::make('actions', '', 'pages');
        $data = $column->toArray();

        expect($data['header']['value'])
            ->toContain('type="checkbox"')
            ->toContain('invertAll(this, this.form)');

        $html = $data['data']['function'](['id' => 9])->toHtml();

        expect($data['data']['class'])->toBe('centertext')
            ->and($html)
            ->toContain('type="checkbox"')
            ->toContain('value="9"')
            ->toContain('name="pages[]"');
    });
});

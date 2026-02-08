<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Enums\Status;
use LightPortal\UI\Tables\StatusColumn;

beforeEach(function () {
    Lang::$txt['status'] = 'Status';
    Lang::$txt['lp_action_off'] = 'Off';
    Lang::$txt['lp_action_on'] = 'On';
});

describe('StatusColumn', function () {
    it('renders status toggle markup', function () {
        $column = StatusColumn::make();
        $data = $column->toArray();
        $html = $data['data']['function'](['id' => 3, 'status' => Status::ACTIVE->value]);

        expect($data['header']['value'])->toBe('Status')
            ->and($html)
            ->toContain('data-id="3"')
            ->toContain('entity.toggleStatus')
            ->and($data['sort']['default'])->toBe('status DESC')
            ->and($data['sort']['reverse'])->toBe('status');
    });
});

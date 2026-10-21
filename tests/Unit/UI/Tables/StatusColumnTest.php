<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\Status;
use LightPortal\UI\Tables\StatusColumn;

beforeEach(function () {
    Lang::$txt['status'] = 'Status';
    Lang::$txt['lp_action_off'] = 'Off';
    Lang::$txt['lp_action_on'] = 'On';

    Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_categories&actions&session=token';
});

describe('StatusColumn', function () {
    it('renders status toggle markup', function () {
        $column = StatusColumn::make();
        $data   = $column->toArray();
        $html   = $data['data']['function'](['id' => 3, 'status' => Status::ACTIVE->value]);

        expect($data['header']['value'])->toBe('Status')
            ->and($html)
            ->toContain('data-id="3"')
            ->toContain('data-signals="{ st_3: true }"')
            ->and($data['sort']['default'])->toBe('status DESC')
            ->and($data['sort']['reverse'])->toBe('status');
    });

    it('renders the toggle as a Datastar request', function () {
        $column = StatusColumn::make();
        $html   = $column->toArray()['data']['function'](['id' => 5, 'status' => Status::ACTIVE->value]);

        expect($html)
            ->toContain('data-class:on="$st_5"')
            ->toContain('data-class:off="!$st_5"')
            ->toContain('data-attr:title="$st_5 ? \'Off\' : \'On\'"')
            ->toContain('@post(\'https://example.com/index.php?action=admin&area=lp_categories&actions&session=token\', {payload: {toggle_item: 5}})');
    });

    it('marks inactive entries as false', function () {
        $column = StatusColumn::make();
        $html   = $column->toArray()['data']['function'](['id' => 9, 'status' => Status::INACTIVE->value]);

        expect($html)->toContain('data-signals="{ st_9: false }"');
    });
});

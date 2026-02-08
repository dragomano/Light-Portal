<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\EntryType;
use LightPortal\UI\Tables\PageTypeSelectRow;

beforeEach(function () {
    Lang::$txt['lp_page_type'] = 'Type';

    Utils::$context['lp_page_types'] = [
        EntryType::DEFAULT->name() => 'Default',
        EntryType::INTERNAL->name() => 'Internal',
    ];
    Utils::$context['user'] = ['is_admin' => false];
    Utils::$context['lp_selected_page_type'] = EntryType::DEFAULT->name();
});

describe('PageTypeSelectRow', function () {
    it('excludes internal type for non-admin users', function () {
        Utils::$context['user']['is_admin'] = false;

        $row = PageTypeSelectRow::make();
        $value = $row->toArray()['value'];

        expect($value)->not()->toContain('Internal');
    });

    it('includes internal type for admin users', function () {
        Utils::$context['user']['is_admin'] = true;

        $row = PageTypeSelectRow::make();
        $value = $row->toArray()['value'];

        expect($value)->toContain('Internal');
    });
});

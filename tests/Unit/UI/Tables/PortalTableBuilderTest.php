<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\TableBuilder;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\UI\Tables\PortalTableBuilderInterface;

beforeEach(function () {
    Lang::$txt['lp_no_items'] = 'No items found';
    Utils::$context['session_var'] = 'session_var';
    Utils::$context['session_id'] = 'session_id';
    Config::$scripturl = 'https://example.com/index.php';
});

describe('PortalTableBuilder', function () {
    it('implements PortalTableBuilderInterface', function () {
        expect(PortalTableBuilder::class)->toImplement(PortalTableBuilderInterface::class);
    });

    it('extends TableBuilder', function () {
        expect(PortalTableBuilder::class)->toExtend(TableBuilder::class);
    });

    it('has withCreateButton method', function () {
        expect(PortalTableBuilder::class)->toHaveMethod('withCreateButton');
    });

    describe('make()', function () {
        it('creates instance with id and title', function () {
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            expect($builder)->toBeInstanceOf(PortalTableBuilder::class);
        });

        it('sets default pagination to 20', function () {
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            expect($builder)->toBeInstanceOf(PortalTableBuilder::class);
        });

        it('sets no items label from lang', function () {
            Lang::$txt['lp_no_items'] = 'Custom no items text';
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            expect($builder)->toBeInstanceOf(PortalTableBuilder::class);
        });
    });

    describe('withCreateButton()', function () {
        it('returns new instance with create button', function () {
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            $result = $builder->withCreateButton('pages');
            expect($result)->toBeInstanceOf(PortalTableBuilder::class);
        });

        it('returns instance with custom title', function () {
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            $result = $builder->withCreateButton('pages', 'Add New Page');
            expect($result)->toBeInstanceOf(PortalTableBuilder::class);
        });

        it('returns instance with entity parameter', function () {
            $builder = PortalTableBuilder::make('test_table', 'Test Table');
            $result = $builder->withCreateButton('categories');
            expect($result)->toBeInstanceOf(PortalTableBuilder::class);
        });
    });
});

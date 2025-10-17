<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\TitleClass;

it('works with real ContentClass enum', function () {
    $values = ContentClass::values();

    expect($values)->toBeArray()
        ->and($values)->toHaveCount(12) // ContentClass has 12 cases
        ->and($values)->toHaveKey('roundframe')
        ->and($values)->toHaveKey('windowbg')
        ->and($values)->toHaveKey('bbc_code')
        ->and($values)->toHaveKey(''); // EMPTY case

    $first = ContentClass::first();
    expect($first)->toBe('roundframe');
});

it('works with real TitleClass enum', function () {
    $values = TitleClass::values();

    expect($values)->toBeArray()
        ->and($values)->toHaveCount(10) // TitleClass has 10 cases
        ->and($values)->toHaveKey('cat_bar')
        ->and($values)->toHaveKey('title_bar')
        ->and($values)->toHaveKey('progress_bar')
        ->and($values)->toHaveKey(''); // EMPTY case

    $first = TitleClass::first();
    expect($first)->toBe('cat_bar');
});

it('checks that ContentClass has correct cases count', function () {
    $cases = ContentClass::cases();

    expect($cases)->toBeArray()
        ->and($cases)->toHaveCount(12);

    // Check that we have expected case names (they come as objects, not strings)
    $caseNames = array_map(fn($case) => $case->name, $cases);
    expect($caseNames)->toContain('ROUNDFRAME')
        ->and($caseNames)->toContain('WINDOWBG')
        ->and($caseNames)->toContain('EMPTY');
});

it('checks that TitleClass has correct cases count', function () {
    $cases = TitleClass::cases();

    expect($cases)->toBeArray()
        ->and($cases)->toHaveCount(10);

    // Check that we have expected case names (they come as objects, not strings)
    $caseNames = array_map(fn($case) => $case->name, $cases);
    expect($caseNames)->toContain('CAT_BAR')
        ->and($caseNames)->toContain('TITLE_BAR')
        ->and($caseNames)->toContain('EMPTY');
});

it('checks that ContentClass generates correct HTML for specific cases', function () {
    $html = ContentClass::html('Test content', 'roundframe');
    expect($html)->toContain('Test content');

    $html = ContentClass::html('Test content', 'windowbg');
    expect($html)->toContain('Test content');

    $html = ContentClass::html('Test content', 'bbc_code');
    expect($html)->toContain('Test content');
});

it('checks that TitleClass generates correct HTML for specific cases', function () {
    $html = TitleClass::html('Test content', 'cat_bar');
    expect($html)->toContain('Test content');

    $html = TitleClass::html('Test content', 'title_bar');
    expect($html)->toContain('Test content');

    $html = TitleClass::html('Test content', 'progress_bar');
    expect($html)->toContain('Test content');
});

it('returns HTML for empty class when unknown class is provided', function () {
    $html = ContentClass::html('Test content', 'unknown_class');
    expect($html)->toContain('Test content'); // Uses default empty class

    $html = TitleClass::html('Test content', 'unknown_class');
    expect($html)->toContain('Test content'); // Uses default empty class
});

it('handles empty class correctly', function () {
    $html = ContentClass::html('Test content');
    expect($html)->toContain('Test content');

    $html = TitleClass::html('Test content');
    expect($html)->toContain('Test content');
});

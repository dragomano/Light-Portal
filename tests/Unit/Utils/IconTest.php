<?php

declare(strict_types=1);

use LightPortal\Utils\Icon;

arch()
    ->expect(Icon::class)
    ->toHaveMethods(['get', 'parse', 'all']);

it('parses icon with valid FontAwesome class', function () {
    $result = Icon::parse('fas fa-home');
    expect($result)->toContain('class="fas fa-home"');
});

it('parses icon with invalid FontAwesome class', function () {
    $result = Icon::parse('invalid-icon');
    expect($result)->toContain('class="invalid-icon"');
});

it('returns empty string for null icon', function () {
    $result = Icon::parse(null);
    expect($result)->toBe('');
});

it('handles InvalidArgumentException gracefully', function () {
    $result = Icon::parse('invalid-icon');
    expect($result)->toContain('class="invalid-icon"');
});

<?php

declare(strict_types=1);

use LightPortal\Utils\Str;

arch()
    ->expect(Str::class)
    ->toHaveMethods([
        'cleanBbcode',
        'getSnakeName',
        'getCamelName',
        'getTeaser',
        'getImageFromText',
        'decodeHtmlEntities',
        'html',
        'typed',
    ]);

describe('Str::getCamelName()', function () {
    it('converts snake_case to CamelCase', function () {
        $result = Str::getCamelName('hello_world');

        expect($result)->toBe('HelloWorld');
    });

    it('converts single word to capitalized', function () {
        $result = Str::getCamelName('hello');

        expect($result)->toBe('Hello');
    });

    it('handles multiple underscores', function () {
        $result = Str::getCamelName('a_b_c_d');

        expect($result)->toBe('ABCD');
    });

    it('handles already camelCase input', function () {
        $result = Str::getCamelName('helloWorld');

        expect($result)->toBe('HelloWorld');
    });

    it('handles leading underscore', function () {
        $result = Str::getCamelName('_private_var');

        expect($result)->toBe('PrivateVar');
    });
});

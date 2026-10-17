<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use LightPortal\Assets\CssCollection;
use LightPortal\Assets\ImageCollection;
use LightPortal\Assets\ScriptCollection;

beforeEach(function () {
    Config::$boardurl = 'https://example.com';
});

it('builds local urls for css files', function () {
    $collection = new CssCollection('HelloPortal');

    $collection->add('style.css');

    expect($collection->toArray())->toBe([
        'https://example.com/Sources/LightPortal/Plugins/HelloPortal/style.css',
    ]);
});

it('keeps external urls as is', function () {
    $collection = new ScriptCollection('HelloPortal');

    $collection->add('https://cdn.example.com/lib.js')
        ->add('//cdn.example.com/lib2.js');

    expect($collection->toArray())->toBe([
        'https://cdn.example.com/lib.js',
        '//cdn.example.com/lib2.js',
    ]);
});

it('adds multiple files at once', function () {
    $collection = new ScriptCollection('TestPlugin');

    $result = $collection->addMultiple(['a.js', 'b.js']);

    expect($collection->toArray())->toBe([
        'https://example.com/Sources/LightPortal/Plugins/TestPlugin/a.js',
        'https://example.com/Sources/LightPortal/Plugins/TestPlugin/b.js',
    ])
        ->and($result)->toBe($collection);
});

it('returns the collection type', function () {
    expect((new CssCollection('Test'))->getType())->toBe('css')
        ->and((new ScriptCollection('Test'))->getType())->toBe('scripts')
        ->and((new ImageCollection('Test'))->getType())->toBe('images');
});

it('supports fluent chaining', function () {
    $collection = new ImageCollection('Test');

    $result = $collection->add('a.png')->add('b.png');

    expect($result)->toBe($collection)
        ->and($collection->toArray())->toHaveCount(2);
});

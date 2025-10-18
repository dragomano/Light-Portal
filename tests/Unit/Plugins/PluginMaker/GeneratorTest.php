<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../src/Sources/LightPortal/Plugins/PluginMaker/Generator.php';

use LightPortal\Plugins\PluginMaker\Generator;
use Nette\PhpGenerator\Literal;
use Tests\ReflectionAccessor;

dataset('getPluginType cases', [
    'empty types array'                   => [[], null],
    'only other type'                     => [['other'], null],
    'single article type'                 => [['article'], 'PluginType::ARTICLE'],
    'single block type'                   => [['block'], null],
    'single comment type'                 => [['comment'], 'PluginType::COMMENT'],
    'multiple types'                      => [['article', 'block', 'comment'], ['PluginType::ARTICLE', 'PluginType::COMMENT']],
    'multiple types with other'           => [['article', 'other', 'block'], 'PluginType::ARTICLE'],
    'multiple types with block and ssi'   => [['block', 'ssi'], null],
    'multiple types with ssi and block'   => [['ssi', 'block'], null],
    'multiple types with block and games' => [['block', 'games'], null],
    'multiple types with games and block' => [['games', 'block'], null],
]);

it('returns correct plugin type', function ($types, $expected) {
    $plugin = ['types' => $types, 'name' => 'TestPlugin', 'icon' => ''];

    $generator = new ReflectionAccessor(new Generator($plugin));

    $result = $generator->callProtectedMethod('getPluginType');

    if ($expected === null) {
        expect($result)->toBeNull();
    } elseif (is_array($expected)) {
        expect($result)->toBeArray()
            ->and(count($result))->toBe(count($expected))
            ->and(array_map(fn($item) => (string) $item, $result))->toBe($expected);
    } else {
        expect($result)->toBeInstanceOf(Literal::class)
            ->and((string) $result)->toBe($expected);
    }
})->with('getPluginType cases');


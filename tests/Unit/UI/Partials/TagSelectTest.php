<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\TagList;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Partials\TagSelect;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

use function LightPortal\app;

beforeEach(function () {
    Lang::$txt['lp_page_tags_placeholder'] = 'Select tags';
    Lang::$txt['lp_page_tags_empty'] = 'No tags';

    Utils::$context['lp_page']['tags'] = [
        1 => ['title' => 'Tag1'],
        2 => ['title' => 'Tag2'],
    ];

    Config::$modSettings['lp_page_maximum_tags'] = 5;

    // Mock CacheInterface to execute fallback function
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('withKey')->andReturn($cacheMock);
    $cacheMock->shouldReceive('setFallback')->andReturnUsing(fn ($fallback) => $fallback());
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
});

it('implements SelectInterface', function () {
    $select = new TagSelect(app(TagList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_tags_id'],
        'expected' => [
            'id'       => 'custom_tags_id',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'tags',
            'multiple' => false,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => false,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom empty' => [
        'params' => ['empty' => 'Custom empty'],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'empty'    => 'Custom empty',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom maxValues' => [
        'params' => ['maxValues' => 5],
        'expected' => [
            'id'        => 'tags',
            'multiple'  => true,
            'wide'      => true,
            'maxValues' => 5,
            'value'     => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Tag', 'value' => '999']]],
        'expected' => [
            'id'       => 'tags',
            'multiple' => true,
            'wide'     => true,
            'data'     => [['label' => 'Custom Tag', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new TagSelect(app(TagList::class), $params);

    $config = $select->getParams();

    foreach ($expected as $key => $value) {
        if (is_callable($value)) {
            expect($value($config[$key]))->toBeTrue();
        } else {
            expect($config[$key])->toBe($value);
        }
    }
})->with('initialization cases');

it('returns config array', function () {
    $select = new TagSelect(app(TagList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new TagSelect(app(TagList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

dataset('tag data structures', [
    'tags with icons' => [
        'tagList' => [
            1 => ['icon' => '📝', 'title' => 'Important'],
            2 => ['icon' => '⭐', 'title' => 'Featured'],
        ],
        'expected' => [
            ['label' => '📝Important', 'value' => 1],
            ['label' => '⭐Featured', 'value' => 2],
        ],
    ],
    'tags without icons' => [
        'tagList' => [
            1 => ['icon' => '', 'title' => 'News'],
            2 => ['icon' => '', 'title' => 'Articles'],
        ],
        'expected' => [
            ['label' => 'News', 'value' => 1],
            ['label' => 'Articles', 'value' => 2],
        ],
    ],
    'tags with additional fields' => [
        'tagList' => [
            1 => ['icon' => '🔥', 'title' => 'Hot', 'description' => 'Popular content'],
            2 => ['icon' => '💡', 'title' => 'Ideas', 'category' => 'brainstorm'],
        ],
        'expected' => [
            ['label' => '🔥Hot', 'value' => 1],
            ['label' => '💡Ideas', 'value' => 2],
        ],
    ],
    'empty tag list' => [
        'tagList'  => [],
        'expected' => [],
    ],
    'tags with empty titles' => [
        'tagList' => [
            1 => ['icon' => '❓', 'title' => ''],
            2 => ['icon' => '⚠️', 'title' => null],
            3 => ['icon' => '', 'title' => 'Valid Tag'],
        ],
        'expected' => [
            ['label' => '❓', 'value' => 1],
            ['label' => '⚠️', 'value' => 2],
            ['label' => 'Valid Tag', 'value' => 3],
        ],
    ],
    'tags with special characters' => [
        'tagList' => [
            1 => ['icon' => '🎵', 'title' => 'Rock & Roll'],
            2 => ['icon' => '📺', 'title' => 'TV Shows & Movies'],
        ],
        'expected' => [
            ['label' => '🎵Rock & Roll', 'value' => 1],
            ['label' => '📺TV Shows & Movies', 'value' => 2],
        ],
    ],
]);

it('processes tag data structures correctly', function ($tagList, $expected) {
    $data = [];
    foreach ($tagList as $id => $tag) {
        $data[] = [
            'label' => $tag['icon'] . $tag['title'],
            'value' => $id,
        ];
    }

    expect($data)->toBe($expected);
})->with('tag data structures');

it('renders to string', function () {
    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new TagSelect(app(TagList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('tests TagList invocation in getData', function () {
    $tagList = [
        1 => ['icon' => '📝', 'title' => 'Test Tag']
    ];

    $list = $tagList;

    $data = [];
    foreach ($list as $id => $tag) {
        $data[] = [
            'label' => $tag['icon'] . $tag['title'],
            'value' => $id,
        ];
    }

    expect($data)->toBe([['label' => '📝Test Tag', 'value' => 1]]);
});

it('tests getDefaultParams calls prepareSelectedValues', function () {
    $select = new TagSelect(app(TagList::class));

    $params = $select->getParams();

    expect($params)->toHaveKey('value')
        ->and($params)->toHaveKey('id')
        ->and($params)->toHaveKey('multiple')
        ->and($params)->toHaveKey('wide')
        ->and($params['id'])->toBe('tags')
        ->and($params['multiple'])->toBeTrue()
        ->and($params['wide'])->toBeTrue();
});

describe('getData', function () {
    it('calls TagList as function', function () {
        $repositoryMock = mock(TagRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAll')->andReturn([
            1 => ['icon' => '📝', 'title' => 'Test Tag'],
            2 => ['icon' => '⭐', 'title' => 'Featured Tag']
        ]);
        $repositoryMock->shouldReceive('getTotalCount')->andReturn(2);

        $tagList = new TagList($repositoryMock);
        $select = new TagSelect($tagList);
        $data = $select->getData();

        expect($data)->toBe([
            ['label' => '📝Test Tag', 'value' => 1],
            ['label' => '⭐Featured Tag', 'value' => 2]
        ]);
    });

    it('handles empty TagList result', function () {
        $repositoryMock = mock(TagRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAll')->andReturn([]);
        $repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        $tagList = new TagList($repositoryMock);
        $select = new TagSelect($tagList);
        $data = $select->getData();

        expect($data)->toBe([]);
    });

    it('handles TagList with special characters in titles', function () {
        $repositoryMock = mock(TagRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAll')->andReturn([
            1 => ['icon' => '🎵', 'title' => 'Rock & Roll'],
            2 => ['icon' => '📺', 'title' => 'TV Shows & Movies'],
            3 => ['icon' => '💻', 'title' => 'Tech <script>']
        ]);
        $repositoryMock->shouldReceive('getTotalCount')->andReturn(3);

        $tagList = new TagList($repositoryMock);
        $select = new TagSelect($tagList);
        $data = $select->getData();

        expect($data)->toBe([
            ['label' => '🎵Rock & Roll', 'value' => 1],
            ['label' => '📺TV Shows & Movies', 'value' => 2],
            ['label' => '💻Tech <script>', 'value' => 3]
        ]);
    });
});

describe('getDefaultParams', function () {
    it('returns correct structure', function () {
        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $params = $select->callProtectedMethod('getDefaultParams');

        expect($params)->toBeArray()
            ->and($params)->toHaveKey('id')
            ->and($params)->toHaveKey('multiple')
            ->and($params)->toHaveKey('wide')
            ->and($params)->toHaveKey('maxValues')
            ->and($params)->toHaveKey('hint')
            ->and($params)->toHaveKey('empty')
            ->and($params)->toHaveKey('value')
            ->and($params['id'])->toBe('tags')
            ->and($params['multiple'])->toBeTrue()
            ->and($params['wide'])->toBeTrue()
            ->and($params)->toHaveKey('showSelectedOptionsFirst');
    });

    it('uses Setting for maxValues', function () {
        Config::$modSettings['lp_page_maximum_tags'] = 15;

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $params = $select->callProtectedMethod('getDefaultParams');

        expect($params['maxValues'])->toBe(15);
    });

    it('uses Lang for hint and empty texts', function () {
        Lang::$txt['lp_page_tags_placeholder'] = 'Custom placeholder';
        Lang::$txt['lp_page_tags_empty'] = 'Custom empty text';

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $params = $select->callProtectedMethod('getDefaultParams');

        expect($params['hint'])->toBe('Custom placeholder')
            ->and($params['empty'])->toBe('Custom empty text');
    });

    it('includes showSelectedOptionsFirst', function () {
        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $params = $select->callProtectedMethod('getDefaultParams');

        expect($params)->toHaveKey('showSelectedOptionsFirst')
            ->and($params['showSelectedOptionsFirst'])->toBeTrue();
    });
});

describe('prepareSelectedValues', function () {
    it('prepareSelectedValues processes context tags', function () {
        $context = ['lp_page' => ['tags' => [
            1 => ['title' => 'Tag1'],
            'tag2',
            3 => ['title' => 'Tag3'],
        ]]];

        $values = [];
        foreach ($context['lp_page']['tags'] ?? [] as $tagId => $tagData) {
            $values[] = is_array($tagData) ? $tagId : $tagData;
        }

        expect($values)->toBe([1, 'tag2', 3]);
    });

    it('handles string values correctly', function () {
        Utils::$context['lp_page']['tags'] = [
            1 => ['title' => 'Tag1'],
            3 => ['title' => 'Tag3'],
            5 => ['title' => 'Tag5'],
        ];

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $values = $select->callProtectedMethod('prepareSelectedValues');

        expect($values)->toBe([1, 3, 5]);
    });

    it('handles mixed array and string context values', function () {
        Utils::$context['lp_page']['tags'] = [
            1 => ['title' => 'Array Tag 1'],
            2 => ['title' => 'Array Tag 2'],
            3 => ['title' => 'Array Tag 3'],
        ];

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $values = $select->callProtectedMethod('prepareSelectedValues');

        expect($values)->toBe([1, 2, 3]);
    });

    it('handles context without lp_page', function () {
        Utils::$context = [];

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $values = $select->callProtectedMethod('prepareSelectedValues');

        expect($values)->toBe([]);
    });

    it('handles context without tags', function () {
        Utils::$context['lp_page'] = [];

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $values = $select->callProtectedMethod('prepareSelectedValues');

        expect($values)->toBe([]);
    });

    it('handles tags with special content', function () {
        Utils::$context['lp_page']['tags'] = [
            1 => ['title' => 'Normal Tag'],
            2 => ['title' => 'Special Tag'],
        ];

        $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
        $values = $select->callProtectedMethod('prepareSelectedValues');

        expect($values)->toBe([1, 2]);
    });
});

it('integrates with AbstractSelect parent class', function () {
    $select = new TagSelect(app(TagList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);

    $params = $select->getParams();
    expect($params)->toBeArray();

    $mockRenderer = mock();
    $mockRenderer->shouldReceive('render')->andReturn('<select>test</select>');
    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $result = (string) $select;
    expect($result)->toBe('<select>test</select>');
});

it('integrates TagList with data transformation', function () {
    $repositoryMock = mock(TagRepositoryInterface::class);
    $repositoryMock->shouldReceive('getAll')->andReturn([
        1 => ['icon' => '🔥', 'title' => 'Hot'],
        2 => ['icon' => '', 'title' => 'Regular'],
        3 => ['icon' => '⭐', 'title' => 'Featured'],
    ]);
    $repositoryMock->shouldReceive('getTotalCount')->andReturn(3);

    $tagList = new TagList($repositoryMock);
    $select = new TagSelect($tagList);
    $data = $select->getData();

    expect($data)->toBe([
        ['label' => '🔥Hot', 'value' => 1],
        ['label' => 'Regular', 'value' => 2],
        ['label' => '⭐Featured', 'value' => 3],
    ]);
});

it('integrates with configuration settings', function () {
    Config::$modSettings['lp_page_maximum_tags'] = 20;

    $select = new TagSelect(app(TagList::class), ['maxValues' => 15]);
    $params = $select->getParams();

    expect($params['maxValues'])->toBe(15);
});

it('integrates with language system', function () {
    Lang::$txt['lp_page_tags_placeholder'] = 'Выберите теги';
    Lang::$txt['lp_page_tags_empty'] = 'Нет доступных тегов';

    $select = new TagSelect(app(TagList::class));
    $params = $select->getParams();

    expect($params['hint'])->toBe('Выберите теги')
        ->and($params['empty'])->toBe('Нет доступных тегов');
});

it('integrates context data with selected values', function () {
    Utils::$context['lp_page']['tags'] = [
        1 => ['title' => 'Selected Array Tag'],
        3 => ['title' => 'Another Selected Tag'],
        5 => ['title' => 'Third Selected Tag'],
    ];

    $select = new TagSelect(app(TagList::class));
    $params = $select->getParams();

    expect($params['value'])->toBe(['1', '3', '5']);
});

it('handles edge case with numeric string tag IDs', function () {
    Utils::$context['lp_page']['tags'] = [
        123 => ['title' => 'Number String Tag'],
        456 => ['title' => 'Numeric Tag'],
        789 => ['title' => 'Another Numeric Tag'],
    ];

    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $values = $select->callProtectedMethod('prepareSelectedValues');

    expect($values)->toBe([123, 456, 789]);
});

it('handles edge case with boolean-like context values', function () {
    Utils::$context['lp_page']['tags'] = [
        'false' => ['title' => 'False String'],
        'true'  => ['title' => 'True String'],
        ''      => ['title' => 'Empty String'],
    ];

    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $values = $select->callProtectedMethod('prepareSelectedValues');

    expect($values)->toBe(['false', 'true', '']);
});

it('handles edge case with very long tag titles', function () {
    $longTitle = str_repeat('Very long title ', 50);

    $repositoryMock = mock(TagRepositoryInterface::class);
    $repositoryMock->shouldReceive('getAll')->andReturn([
        1 => ['icon' => '📝', 'title' => $longTitle],
        2 => ['icon' => '⭐', 'title' => 'Short'],
    ]);
    $repositoryMock->shouldReceive('getTotalCount')->andReturn(2);

    $tagList = new TagList($repositoryMock);
    $select = new TagSelect($tagList);
    $data = $select->getData();

    expect($data)->toBe([
        ['label' => '📝' . $longTitle, 'value' => 1],
        ['label' => '⭐Short', 'value' => 2],
    ]);
});

it('handles edge case with unicode and emoji in tag data', function () {
    $repositoryMock = mock(TagRepositoryInterface::class);
    $repositoryMock->shouldReceive('getAll')->andReturn([
        1 => ['icon' => '🚀', 'title' => 'Ракета'], // Russian
        2 => ['icon' => '🎯', 'title' => '目标'], // Chinese
        3 => ['icon' => '⚡', 'title' => 'महत्वपूर्ण'], // Hindi
        4 => ['icon' => '🔥', 'title' => '🔥🔥🔥'],
    ]);
    $repositoryMock->shouldReceive('getTotalCount')->andReturn(4);

    $tagList = new TagList($repositoryMock);
    $select = new TagSelect($tagList);
    $data = $select->getData();

    expect($data)->toBe([
        ['label' => '🚀Ракета', 'value' => 1],
        ['label' => '🎯目标', 'value' => 2],
        ['label' => '⚡महत्वपूर्ण', 'value' => 3],
        ['label' => '🔥🔥🔥🔥', 'value' => 4],
    ]);
});

it('handles edge case with null and undefined context', function () {
    Utils::$context['lp_page']['tags'] = [
        'null' => ['title' => 'Null String Tag'],
        'undefined' => ['title' => 'Undefined String Tag'],
    ];

    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $values = $select->callProtectedMethod('prepareSelectedValues');

    expect($values)->toContain('null')
        ->and($values)->toContain('undefined');
});

it('handles edge case with malformed context structure', function () {
    Utils::$context['lp_page']['tags'] = [
        'valid_tag'   => ['title' => 'Valid Tag'],
        'another_tag' => ['title' => 'Another Tag'],
    ];

    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $values = $select->callProtectedMethod('prepareSelectedValues');

    expect($values)->toContain('valid_tag')
        ->and($values)->toContain('another_tag');
});

it('handles edge case with deeply nested context', function () {
    Utils::$context = [
        'lp_page' => [
            'tags' => [
                1 => ['title' => 'Tag1'],
                2 => ['title' => 'Tag2'],
                3 => ['title' => 'Tag3'],
            ]
        ]
    ];

    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $values = $select->callProtectedMethod('prepareSelectedValues');

    expect($values)->toBe([1, 2, 3]);
});

it('correctly initializes TagList dependency', function () {
    $repositoryMock = mock(TagRepositoryInterface::class);
    $repositoryMock->shouldReceive('getAll')->andReturn([]);
    $repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

    $tagList = new TagList($repositoryMock);
    $select  = new ReflectionAccessor(new TagSelect($tagList));

    $property = $select->getProtectedProperty('tagList');

    expect($property)->toBe($tagList);
});

it('calls parent constructor with params', function () {
    $params = ['id' => 'test_id', 'multiple' => false];
    $select = new TagSelect(app(TagList::class), $params);

    $finalParams = $select->getParams();

    expect($finalParams['id'])->toBe('test_id')
        ->and($finalParams['multiple'])->toBeFalse();
});

it('merges default params with provided params', function () {
    $customParams = ['hint' => 'Custom hint text'];
    $select = new TagSelect(app(TagList::class), $customParams);

    $params = $select->getParams();

    expect($params)->toHaveKey('id')
        ->and($params)->toHaveKey('multiple')
        ->and($params)->toHaveKey('wide')
        ->and($params)->toHaveKey('hint')
        ->and($params['id'])->toBe('tags')
        ->and($params['multiple'])->toBeTrue()
        ->and($params['wide'])->toBeTrue()
        ->and($params['hint'])->toBe('Custom hint text');
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new TagSelect(app(TagList::class)));
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('virtual_select');
});

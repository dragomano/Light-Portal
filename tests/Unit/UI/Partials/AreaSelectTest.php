<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Partials\AreaSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_block_areas_subtext'] = 'Select areas';
    Lang::$txt['personal_messages'] = 'Personal Messages';
    Lang::$txt['members_title'] = 'Members';
    Lang::$txt['recent_posts'] = 'Recent Posts';
    Lang::$txt['view_unread_category'] = 'View Unread Category';
    Lang::$txt['unread_replies'] = 'Unread Replies';
    Lang::$txt['forum_stats'] = 'Forum Stats';
    Lang::$txt['who_title'] = 'Who';
    Lang::$txt['terms_and_rules'] = 'Terms and Rules';

    Utils::$context['lp_block']['areas'] = 'home,forum';
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new AreaSelect();

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'areas',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_areas_id'],
        'expected' => [
            'id'       => 'custom_areas_id',
            'multiple' => true,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['home', 'forum']],
        'expected' => [
            'id'       => 'areas',
            'multiple' => true,
            'wide'     => true,
            'value'    => ['home', 'forum'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'areas',
            'multiple' => false,
            'wide'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'areas',
            'multiple' => true,
            'wide'     => false,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'areas',
            'multiple' => true,
            'wide'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Area', 'value' => 'custom']]],
        'expected' => [
            'id'       => 'areas',
            'multiple' => true,
            'wide'     => true,
            'data'     => [['label' => 'Custom Area', 'value' => 'custom']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new AreaSelect($params);

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
    $select = new AreaSelect();

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new AreaSelect();

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new AreaSelect();

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});


it('template is set correctly', function () {
    $select = new AreaSelect();

    $reflection = new ReflectionClass($select);
    $property = $reflection->getProperty('template');

    expect($property->getValue($select))->toBe('virtual_select');
});

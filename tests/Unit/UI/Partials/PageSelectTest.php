<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\UI\Partials\PageSelect;
use Bugo\LightPortal\UI\Partials\SelectInterface;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Tests\AppMockRegistry;

use Tests\ReflectionAccessor;
use function Bugo\LightPortal\app;

beforeEach(function () {
    Lang::$txt['lp_frontpage_pages_select'] = 'Select pages';
    Lang::$txt['lp_frontpage_pages_no_items'] = 'No pages';
});

afterEach(function () {
    Mockery::close();
});

it('implements SelectInterface', function () {
    $select = new PageSelect(app(PageList::class));

    expect($select)->toBeInstanceOf(SelectInterface::class);
});

dataset('initialization cases', [
    'default params' => [
        'params' => [],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom id' => [
        'params' => ['id' => 'custom_pages_id'],
        'expected' => [
            'id'       => 'custom_pages_id',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom value' => [
        'params' => ['value' => ['1', '2']],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'value'    => ['1', '2'],
        ],
    ],
    'custom multiple' => [
        'params' => ['multiple' => false],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => false,
            'wide'     => true,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom wide' => [
        'params' => ['wide' => false],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => false,
            'more'     => true,
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom hint' => [
        'params' => ['hint' => 'Custom hint'],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'hint'     => 'Custom hint',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom empty' => [
        'params' => ['empty' => 'Custom empty'],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'empty'    => 'Custom empty',
            'value'    => fn($value) => is_array($value),
        ],
    ],
    'custom data' => [
        'params' => ['data' => [['label' => 'Custom Page', 'value' => '999']]],
        'expected' => [
            'id'       => 'lp_frontpage_pages',
            'multiple' => true,
            'wide'     => true,
            'more'     => true,
            'data'     => [['label' => 'Custom Page', 'value' => '999']],
            'value'    => fn($value) => is_array($value),
        ],
    ],
]);

it('initializes with params', function ($params, $expected) {
    $select = new PageSelect(app(PageList::class), $params);

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
    $select = new PageSelect(app(PageList::class));

    $config = $select->getParams();

    expect($config)->toBeArray()
        ->and(array_key_exists('id', $config))->toBeTrue()
        ->and(array_key_exists('value', $config))->toBeTrue();
});

it('returns data array', function () {
    $select = new PageSelect(app(PageList::class));

    $data = $select->getData();

    expect($data)->toBeArray();
});

it('renders to string', function () {
    $mockRenderer = Mockery::mock();
    $mockRenderer->shouldReceive('render')
        ->once()
        ->andReturn('<select></select>');

    AppMockRegistry::set(SelectRenderer::class, $mockRenderer);

    $select = new PageSelect(app(PageList::class));

    $result = (string) $select;

    expect($result)->toBe('<select></select>');
});

it('constructs with PageList dependency', function () {
	$pageList = app(PageList::class);
	$select = new PageSelect($pageList);

	expect($select)->toBeInstanceOf(PageSelect::class);
});

it('constructs with PageList dependency using app helper', function () {
	$select = new PageSelect(app(PageList::class));

	expect($select)->toBeInstanceOf(PageSelect::class);
});

it('returns data array from getData method', function () {
	$select = new PageSelect(app(PageList::class));

	$data = $select->getData();

	expect($data)->toBeArray();
});

it('returns correct default params from getDefaultParams', function () {
	$select = new PageSelect(app(PageList::class));

	$params = $select->getParams();

	expect($params['id'])->toBe('lp_frontpage_pages')
		->and($params['multiple'])->toBeTrue()
		->and($params['wide'])->toBeTrue()
		->and($params['more'])->toBeTrue()
		->and($params['hint'])->toBe(Lang::$txt['lp_frontpage_pages_select'])
		->and($params['empty'])->toBe(Lang::$txt['lp_frontpage_pages_no_items']);
});

it('handles Config with lp_frontpage_pages setting', function () {
	$select = new PageSelect(app(PageList::class));

	$params = $select->getParams();

	expect($params['value'])->toBeArray();
});

it('processes data correctly in getData method', function () {
	$select = new PageSelect(app(PageList::class));

	$data = $select->getData();

	expect($data)->toBeArray();

	if (!empty($data)) {
		expect($data[0])->toHaveKey('label')
			->and($data[0])->toHaveKey('value')
			->and($data[0]['value'])->toBeInt();
	}
});

it('verifies getDefaultParams returns all required keys', function () {
	$select = new PageSelect(app(PageList::class));

	$params = $select->getParams();

	expect($params)->toHaveKey('id')
		->and($params)->toHaveKey('multiple')
		->and($params)->toHaveKey('wide')
		->and($params)->toHaveKey('more')
		->and($params)->toHaveKey('hint')
		->and($params)->toHaveKey('empty')
		->and($params)->toHaveKey('value');
});

it('verifies getDefaultParams returns correct default values', function () {
	$select = new PageSelect(app(PageList::class));

	$params = $select->getParams();

	expect($params['id'])->toBe('lp_frontpage_pages')
		->and($params['multiple'])->toBeTrue()
		->and($params['wide'])->toBeTrue()
		->and($params['more'])->toBeTrue()
		->and($params['value'])->toBeArray();
});

it('verifies getDefaultParams hint and empty use language strings', function () {
	$select = new PageSelect(app(PageList::class));

	$params = $select->getParams();

	expect($params['hint'])->toBeString()
		->and($params['empty'])->toBeString()
		->and($params['hint'])->not()->toBeEmpty()
		->and($params['empty'])->not()->toBeEmpty();
});

it('ensures getData method calls PageList correctly', function () {
	$pageList = app(PageList::class);
	$select = new PageSelect($pageList);

	$data = $select->getData();

	expect($data)->toBeArray();
});

it('tests getData with parent constructor initialization', function () {
	$select = new PageSelect(app(PageList::class));

	$data = $select->getData();
	$params = $select->getParams();

	expect($data)->toBeArray()
		->and($params)->toBeArray();
});

it('correctly sets the template', function () {
    $select = new ReflectionAccessor(new PageSelect(app(PageList::class)));
    $property = $select->getProtectedProperty('template');

    expect($property)->toBe('virtual_select');
});

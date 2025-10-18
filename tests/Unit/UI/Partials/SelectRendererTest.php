<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\View;

use Tests\ReflectionAccessor;
use function LightPortal\app;

beforeEach(function () {
    Lang::$txt = array_merge(Lang::$txt, [
        'no_matches'   => 'No matches',
        'search'       => 'Search',
        'all'          => 'All',
        'remove'       => 'Remove',
        'check_all'    => 'Check all',
        'post_options' => 'Post options',
        'no'           => 'No',
        'lp_example'   => 'Example',
    ]);

    Utils::$context = array_merge(Utils::$context, [
        'right_to_left' => false,
    ]);

    $this->testClass = new class implements SelectInterface {
        private array $params;

        private array $data;

        public function __construct(array $params = [], array $data = [])
        {
            $this->params = array_merge([
                'id'        => 'test_id',
                'multiple'  => false,
                'search'    => true,
                'hint'      => '',
                'value'     => '',
                'disabled'  => false,
                'empty'     => false,
                'wide'      => true,
                'allowNew'  => false,
                'more'      => false,
                'maxValues' => null,
                'showSelectedOptionsFirst' => false,
            ], $params);

            $this->data = $data;
        }

        public function getParams(): array
        {
            return $this->params;
        }

        public function getData(): array
        {
            return $this->data;
        }

        public function __toString(): string
        {
            return '';
        }
    };
});

it('builds init options for virtual select template', function () {
    $config = [
        'id'                       => 'test_id',
        'multiple'                 => true,
        'search'                   => false,
        'hint'                     => 'Custom hint',
        'value'                    => 'option1,option2',
        'disabled'                 => true,
        'empty'                    => 'No options',
        'wide'                     => false,
        'allowNew'                 => true,
        'more'                     => true,
        'maxValues'                => 3,
        'showSelectedOptionsFirst' => true,
        'data'                     => [
            ['label' => 'Option 1', 'value' => 'opt1'],
            ['label' => 'Option 2', 'value' => 'opt2'],
        ]
    ];
    $template = 'virtual_select';
    $templateData = [
        'id'      => 'test_id',
        'data'    => $config['data'],
        'config'  => $config,
        'txt'     => Lang::$txt,
        'context' => Utils::$context,
    ];

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildInitOptions', [$config, $template, $templateData]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('ele')
        ->and($result)->toHaveKey('multiple')
        ->and($result)->toHaveKey('search')
        ->and($result)->toHaveKey('placeholder')
        ->and($result)->toHaveKey('options')
        ->and($result)->toHaveKey('selectedValue')
        ->and($result)->toHaveKey('disabled')
        ->and($result)->toHaveKey('noOptionsText')
        ->and($result)->toHaveKey('allowNewOption')
        ->and($result)->toHaveKey('moreText')
        ->and($result)->toHaveKey('maxValues')
        ->and($result)->toHaveKey('showSelectedOptionsFirst')
        ->and($result['ele'])->toBe('#test_id')
        ->and($result['multiple'])->toBeTrue()
        ->and($result['search'])->toBeFalse()
        ->and($result['placeholder'])->toBe('Custom hint')
        ->and($result['options'])->toBe($config['data'])
        ->and($result['selectedValue'])->toBe('option1,option2')
        ->and($result['disabled'])->toBeTrue()
        ->and($result['noOptionsText'])->toBe('No options')
        ->and($result['allowNewOption'])->toBeTrue()
        ->and($result['moreText'])->toBe('Post options')
        ->and($result['maxValues'])->toBe(3)
        ->and($result['showSelectedOptionsFirst'])->toBeTrue();

});

it('builds init options for preview select template', function () {
    $config = ['id' => 'test_id'];
    $template = 'preview_select';
    $templateData = [
        'id'      => 'test_id',
        'data'    => [],
        'config'  => $config,
        'txt'     => Lang::$txt,
        'context' => Utils::$context,
    ];

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildInitOptions', [$config, $template, $templateData]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('showSelectedOptionsFirst')
        ->and($result)->toHaveKey('optionHeight')
        ->and($result['showSelectedOptionsFirst'])->toBeTrue()
        ->and($result['optionHeight'])->toBe('60px');
});

it('builds init options for icon select template', function () {
    $config = ['id' => 'test_id'];
    $template = 'icon_select';
    $templateData = [
        'id'      => 'test_id',
        'data'    => [],
        'config'  => $config,
        'txt'     => Lang::$txt,
        'context' => Utils::$context,
    ];

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildInitOptions', [$config, $template, $templateData]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('allowNewOption')
        ->and($result['allowNewOption'])->toBeTrue();
});

it('tests generateId method with reflection', function () {
    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $select = new $this->testClass(['id' => 'custom_id']);
    $result = $renderer->callProtectedMethod('generateId', [$select]);

    expect($result)->toContain('lp_select_renderer_test');
});

it('tests formatPrettyOptions method with reflection', function () {
    $options = [
        'ele'      => '#test_id',
        'multiple' => false,
        'search'   => true,
    ];

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('formatPrettyOptions', [$options]);

    expect($result)->toBeString()
        ->and($result)->toContain('"ele": "#test_id"')
        ->and($result)->toContain('"multiple": false')
        ->and($result)->toContain('"search": true');
});

it('tests buildInitOptions method with reflection', function () {
    $config = ['id' => 'test_id', 'multiple' => true];
    $template = 'virtual_select';
    $templateData = [
        'id'      => 'test_id',
        'data'    => [],
        'config'  => $config,
        'txt'     => Lang::$txt,
        'context' => Utils::$context,
    ];

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildInitOptions', [$config, $template, $templateData]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('ele')
        ->and($result)->toHaveKey('multiple')
        ->and($result)->toHaveKey('search')
        ->and($result['ele'])->toBe('#test_id')
        ->and($result['multiple'])->toBeTrue();
});

it('tests buildPreviewSelectOptions method with reflection', function () {
    $config = ['id' => 'test_id'];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildPreviewSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('showSelectedOptionsFirst')
        ->and($result)->toHaveKey('optionHeight')
        ->and($result['showSelectedOptionsFirst'])->toBeTrue()
        ->and($result['optionHeight'])->toBe('60px');
});

it('tests buildIconSelectOptions method with reflection', function () {
    $config = ['id' => 'test_id'];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildIconSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('allowNewOption')
        ->and($result['allowNewOption'])->toBeTrue();
});

it('tests buildPageIconSelectOptions method with reflection', function () {
    $config = ['id' => 'test_id'];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildPageIconSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('allowNewOption')
        ->and($result['allowNewOption'])->toBeTrue();
});

it('tests buildVirtualSelectOptions method with reflection', function () {
    $config = ['id' => 'test_id', 'multiple' => true, 'disabled' => true, 'empty' => 'No options'];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildVirtualSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('ele')
        ->and($result)->toHaveKey('multiple')
        ->and($result)->toHaveKey('disabled')
        ->and($result)->toHaveKey('noOptionsText')
        ->and($result['ele'])->toBe('#test_id')
        ->and($result['multiple'])->toBeTrue()
        ->and($result['disabled'])->toBeTrue()
        ->and($result['noOptionsText'])->toBe('No options');
});

it('tests RTL direction in virtual select options', function () {
    Utils::$context['right_to_left'] = true;

    $config = ['id' => 'test_id'];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildVirtualSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toHaveKey('textDirection')
        ->and($result['textDirection'])->toBe('rtl');

    Utils::$context['right_to_left'] = false;
});

it('tests all virtual select options with complete config', function () {
    $config = [
        'id'        => 'test_id',
        'multiple'  => true,
        'search'    => false,
        'hint'      => 'Custom hint',
        'value'     => 'option1,option2',
        'disabled'  => true,
        'empty'     => 'No options',
        'wide'      => false,
        'allowNew'  => true,
        'more'      => true,
        'maxValues' => 3,
        'showSelectedOptionsFirst' => true,
    ];
    $txt = Lang::$txt;
    $context = Utils::$context;
    $id = 'test_id';

    $renderer = new ReflectionAccessor(new SelectRenderer(app(View::class)));

    $result = $renderer->callProtectedMethod('buildVirtualSelectOptions', [$config, $txt, $context, $id]);

    expect($result)->toHaveKey('multiple')
        ->and($result)->toHaveKey('search')
        ->and($result)->toHaveKey('placeholder')
        ->and($result)->toHaveKey('selectedValue')
        ->and($result)->toHaveKey('disabled')
        ->and($result)->toHaveKey('noOptionsText')
        ->and($result)->toHaveKey('allowNewOption')
        ->and($result)->toHaveKey('moreText')
        ->and($result)->toHaveKey('maxValues')
        ->and($result)->toHaveKey('showSelectedOptionsFirst')
        ->and($result['multiple'])->toBeTrue()
        ->and($result['search'])->toBeFalse()
        ->and($result['placeholder'])->toBe('Custom hint')
        ->and($result['selectedValue'])->toBe('option1,option2')
        ->and($result['disabled'])->toBeTrue()
        ->and($result['noOptionsText'])->toBe('No options')
        ->and($result)->not->toHaveKey('maxWidth')
        ->and($result['allowNewOption'])->toBeTrue()
        ->and($result['moreText'])->toBe($txt['post_options'])
        ->and($result['maxValues'])->toBe(3)
        ->and($result['showSelectedOptionsFirst'])->toBeTrue();
});

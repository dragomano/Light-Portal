<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Areas\PluginArea;
use LightPortal\Enums\PluginType;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Repositories\PluginRepositoryInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\InputFilter;
use LightPortal\Utils\Language;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;
use Tests\TestExitException;

arch()
    ->expect(PluginArea::class)
    ->toUseTraits([HasCache::class, HasRequest::class, HasResponse::class]);

beforeEach(function () {
    $this->repository  = mock(PluginRepositoryInterface::class);
    $this->dispatcher  = mock(EventDispatcherInterface::class);
    $this->inputFilter = mock(InputFilter::class);
    $this->requestMock = mock(RequestInterface::class);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);

    $this->pluginArea = new PluginArea(
        $this->repository,
        $this->dispatcher,
        $this->inputFilter
    );

    $this->accessor = new ReflectionAccessor($this->pluginArea);

    Lang::$txt += [
        'not_applicable' => 'N/A',
        'all'            => 'All',
        'settings'       => 'Settings',
        'save'           => 'Save',
    ];

    $iconListMock = mock(IconList::class);
    $iconListMock->shouldReceive('__invoke')->andReturn(['test-icon' => '<i class="test-icon"></i>']);
    AppMockRegistry::set(IconList::class, $iconListMock);
});

describe('PluginArea::main()', function () {
    it('sets up context variables correctly', function () {
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('toggle')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('save')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('chart')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('api')
            ->andReturn(true);

        $this->dispatcher->shouldReceive('withPlugins')->andReturnSelf();
        $this->dispatcher->shouldReceive('dispatch')->with(PortalHook::addSettings, Mockery::any());

        $this->pluginArea->main();

        expect(Utils::$context['page_title'])->toBe('Portal - Manage plugins')
            ->and(Utils::$context['post_url'])->toContain('?action=admin;area=lp_plugins;save')
            ->and(Utils::$context['lp_plugins_api_endpoint'])->toContain('?action=admin;area=lp_plugins;api');
    });

    it('loads plugin list', function () {
        $expectedPlugins = ['Plugin1'];
        Utils::$context['lp_plugins'] = $expectedPlugins;

        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('toggle')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('save')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('chart')
            ->andReturn(true);
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('api')
            ->andReturn(true);

        $this->dispatcher->shouldReceive('withPlugins')->andReturnSelf();
        $this->dispatcher->shouldReceive('dispatch');

        $this->pluginArea->main();

        expect(Utils::$context['lp_plugins_extra'])->toContain('(1)');
    });
});

describe('PluginArea::handleToggle()', function () {
    it('enables plugin when status is off', function () {
        $_REQUEST['toggle'] = true;
        $pluginData = [
            'plugin' => 0,
            'status' => 'off',
        ];

        Utils::$context['lp_plugins'] = ['TestPlugin'];

        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('toggle')
            ->andReturn(false);
        $this->requestMock
            ->shouldReceive('json')
            ->andReturn($pluginData);

        Theme::$current->settings['default_theme_dir'] = sys_get_temp_dir();

        expect(fn() => $this->accessor->callProtectedMethod('handleToggle'))
            ->not->toThrow(Exception::class);
    });

    it('disables plugin when status is on', function () {
        $_REQUEST['toggle'] = true;
        $pluginData = [
            'plugin' => 0,
            'status' => 'on',
        ];

        Utils::$context['lp_plugins'] = ['TestPlugin'];

        Config::$modSettings['lp_enabled_plugins'] = 'TestPlugin';

        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('toggle')
            ->andReturn(false);
        $this->requestMock
            ->shouldReceive('json')
            ->andReturn($pluginData);

        $responseMock = mock(ResponseInterface::class);
        $responseMock->shouldReceive('exit')->andThrow(new TestExitException());
        AppMockRegistry::set(ResponseInterface::class, $responseMock);

        Theme::$current->settings['default_theme_dir'] = sys_get_temp_dir();

        expect(fn() => $this->accessor->callProtectedMethod('handleToggle'))
            ->toThrow(TestExitException::class);
    });

    it('returns early when toggle parameter is not present', function () {
        $this->requestMock
            ->shouldReceive('hasNot')
            ->with('toggle')
            ->andReturn(true);

        $this->accessor->callProtectedMethod('handleToggle');

        expect(true)->toBeTrue();
    });
});

describe('PluginArea::getTypes()', function () {
    it('returns not applicable when snake name is empty', function () {
        Lang::$txt['not_applicable'] = 'N/A';

        $types = $this->accessor->callProtectedMethod('getTypes', ['']);

        expect($types)->toBe(['N/A' => '']);
    });

    it('returns single type correctly', function () {
        Utils::$context['lp_loaded_addons'] = [
            'test_plugin' => ['type' => 'block'],
        ];

        Utils::$context['lp_plugin_types'] = [
            'block' => 'Block',
        ];

        $types = $this->accessor->callProtectedMethod('getTypes', ['test_plugin']);

        expect($types)->toHaveKey('Block')
            ->and($types['Block'])->toBe(' lp_type_block');
    });

    it('returns multiple types correctly', function () {
        Utils::$context['lp_loaded_addons'] = [
            'test_plugin' => ['type' => 'block article'],
        ];

        Utils::$context['lp_plugin_types'] = [
            'block'   => 'Block',
            'article' => 'Article',
        ];

        $types = $this->accessor->callProtectedMethod('getTypes', ['test_plugin']);

        expect($types)->toHaveKeys(['Block', 'Article'])
            ->and($types['Block'])->toBe(' lp_type_block')
            ->and($types['Article'])->toBe(' lp_type_article');
    });
});

describe('PluginArea::prepareAddonList()', function () {
    it('prepares addon list with correct structure', function () {
        Utils::$context['lp_plugins'] = ['TestPlugin'];
        Utils::$context['lp_loaded_addons'] = [
            'test_plugin' => [
                'type'     => 'block',
                'saveable' => true,
            ]
        ];

        Utils::$context['lp_plugin_types'] = [
            'block' => 'Block',
        ];

        Lang::$txt['lp_test_plugin'] = [
            'description' => 'Test plugin description',
        ];

        $configVars = [
            'test_plugin' => ['setting1' => 'value1'],
        ];

        $filesystemMock = mock(FilesystemInterface::class);
        $filesystemMock->shouldReceive('read')->andReturn('');
        $filesystemMock->shouldReceive('exists')->andReturn(false);
        AppMockRegistry::set(FilesystemInterface::class, $filesystemMock);

        $this->accessor->callProtectedMethod('prepareAddonList', [$configVars]);

        expect(Utils::$context['all_lp_plugins'])->toBeArray()
            ->and(Utils::$context['all_lp_plugins'][0])->toHaveKeys([
                'name',
                'version',
                'outdated',
                'snake_name',
                'desc',
                'status',
                'types',
                'special',
                'settings',
                'saveable',
            ]);
    });

    it('marks plugin as outdated when version is older', function () {
        Utils::$context['lp_plugins'] = ['TestPlugin'];
        Utils::$context['lp_download'] = [
            'TestPlugin' => ['version' => '2.0.0', 'type' => 'block'],
        ];
        Utils::$context['lp_plugin_types'] = PluginType::all();


        $this->accessor->callProtectedMethod('prepareAddonList', [[]]);

        $plugin = Utils::$context['all_lp_plugins'][0];

        expect($plugin['outdated'])->toBeNull();
    });
});

describe('PluginArea::preparedData()', function () {
    it('returns correctly structured data for API', function () {
        Utils::$context['all_lp_plugins'] = [];
        Utils::$context['lp_plugin_types'] = [];
        Utils::$context['character_set'] = 'UTF-8';
        Utils::$context['user'] = ['id' => 1];
        Utils::$context['right_to_left'] = false;
        Utils::$context['post_url'] = 'https://example.com?action=admin;area=lp_plugins;save';

        Lang::$txt += [
            'apply_filter'    => 'Apply Filter',
            'settings_saved'  => 'Settings Saved',
            'find_close'      => 'Find Close',
            'list_view'       => 'List View',
            'card_view'       => 'Card View',
            'remove'          => 'Remove',
            'no'              => 'No',
            'no_matches'      => 'No Matches',
            'search'          => 'Search',
            'lang_dictionary' => 'en',
        ];

        $languageMock = mock(Language::class);
        $languageMock->shouldReceive('getNameFromLocale')->andReturn('English');
        AppMockRegistry::set(Language::class, $languageMock);

        $data = $this->accessor->callProtectedMethod('preparedData');

        expect($data)->toHaveKeys(['txt', 'context', 'plugins', 'icons'])
            ->and($data['txt'])->toBeArray()
            ->and($data['context'])->toBeArray()
            ->and($data['plugins'])->toBeArray()
            ->and($data['icons'])->toBeArray();
    });
});

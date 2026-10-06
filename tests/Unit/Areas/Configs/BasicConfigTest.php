<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Areas\Configs\BasicConfig;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Renderers\RendererInterface;
use LightPortal\Utils\InputFilter;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Lang::$txt['lang_locale']                          = 'en_US';
    Lang::$txt['lp_base']                              = 'Base';
    Lang::$txt['lp_portal']                            = 'Portal';
    Lang::$txt['lp_frontpage_article_sorting_set']     = ['created;desc' => 'Newest'];
    Lang::$txt['lp_show_pagination_set']               = ['default' => 'Default'];
    Lang::$txt['lp_action_on']                         = 'Action on';
    Lang::$txt['lp_example']                           = 'Example: ';
    Lang::$txt['lp_disabled_actions']                  = 'Disabled actions';
    Lang::$txt['lp_template_edit_link']                = 'Edit layout';
    Lang::$txt['groups_light_portal_view']             = 'All';
    Lang::$txt['groups_light_portal_manage_pages_own'] = 'Own';
    Lang::$txt['groups_light_portal_manage_pages_any'] = 'Any';
    Lang::$txt['groups_light_portal_approve_pages']    = 'Approve';
    Lang::$txt['admin.mods_cat_features']              = 'Features';
    Lang::$txt['settings_saved']                       = 'Settings saved';
    Lang::$txt['settings_not_saved']                   = 'Settings not saved';

    Config::$boardurl    = 'https://example.com';
    Config::$scripturl   = 'https://example.com/index.php';
    Config::$modSettings = [];

    Utils::$context['forum_name']    = 'My Forum';
    Utils::$context['session_var']   = 'sess';
    Utils::$context['session_id']    = '12345';
    Utils::$context['character_set'] = 'UTF-8';

    Theme::$current->settings['default_images_url'] = 'https://example.com/Themes/default/images';

    $renderer = mock(RendererInterface::class);
    $renderer->shouldReceive('getLayouts')->andReturn(['default' => 'Default Layout']);
    AppMockRegistry::set(RendererInterface::class, $renderer);

    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('has')->with('save')->andReturn(false)->byDefault();
    $this->request->shouldReceive('hasNot')->with('topic_by_subject')->andReturn(true)->byDefault();
    $this->request->shouldIgnoreMissing();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->response = mock(ResponseInterface::class);
    AppMockRegistry::set(ResponseInterface::class, $this->response);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->byDefault();

    $this->inputFilter = mock(InputFilter::class);

    $this->config = new BasicConfig($this->dispatcher, $this->inputFilter);
});

it('shows basic settings and adds defaults', function () {
    $this->dispatcher->shouldReceive('dispatch')
        ->once()
        ->with(PortalHook::extendBasicConfig, Mockery::on(fn($params) => is_array($params['configVars'])));

    $this->config->show();

    expect(Utils::$context['page_title'])->toBe('Base')
        ->and(Utils::$context['form_action'])->toContain('area=lp_settings;sa=basic')
        ->and(Utils::$context['post_url'])->toContain('area=lp_settings;sa=basic;save')
        ->and(Config::$modSettings['lp_frontpage_title'])->toBe('My Forum')
        ->and(Config::$modSettings['lp_show_views_and_comments'])->toBe(1)
        ->and(Utils::$context['config_vars'])->not->toBeEmpty();
});

it('saves basic settings and filters url parameters', function () {
    $this->request->shouldReceive('has')->with('save')->andReturn(true);

    $this->inputFilter->shouldReceive('filter')->once()->andReturn([
        'lp_image_placeholder' => 'https://example.com/logo.png',
        'lp_standalone_url'    => false,
    ]);

    $post = mock(PostInterface::class);
    $post->shouldReceive('put')->with('lp_image_placeholder', 'https://example.com/logo.png')->once();
    $post->shouldReceive('put')->with('lp_standalone_url', '')->once();
    $post->shouldIgnoreMissing();
    AppMockRegistry::set(PostInterface::class, $post);

    $this->response->shouldReceive('redirect')->once()->with('action=admin;area=lp_settings;sa=basic');

    $this->config->show();

    expect(true)->toBeTrue();
});

it('normalizes version strings correctly', function () {
    $accessor = new ReflectionAccessor($this->config);

    expect($accessor->callMethod('normalizeVersion', ['v3.0.0']))->toBe('3.0.0')
        ->and($accessor->callMethod('normalizeVersion', [' 3.0.0 ']))->toBe('3.0.0')
        ->and($accessor->callMethod('normalizeVersion', ['3.0.0 RC 1']))->toBe('3.0.0-rc-1');
});

it('generates columns options', function () {
    $accessor = new ReflectionAccessor($this->config);

    $options = $accessor->callMethod('getColumnsOptions');

    expect($options)->toBeArray()
        ->and($options)->toHaveCount(5);
});

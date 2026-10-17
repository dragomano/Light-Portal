<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\Configs\PanelConfig;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lang_locale'] = 'en_US';
    Lang::$txt['lp_panels']   = 'Panels';

    Config::$modSettings = [];

    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('has')->with('save')->andReturn(false)->byDefault();
    $this->request->shouldIgnoreMissing();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->response = mock(ResponseInterface::class);
    AppMockRegistry::set(ResponseInterface::class, $this->response);
});

it('shows panel configuration and sets default widths', function () {
    (new PanelConfig())->show();

    expect(Utils::$context['page_title'])->toBe('Panels')
        ->and(Utils::$context['post_url'])->toContain('area=lp_settings;sa=panels;save')
        ->and(Utils::$context['sub_template'])->toBe('show_settings')
        ->and(Utils::$context['lp_left_right_width_values'])->toBe([2, 3, 4])
        ->and(Utils::$context['lp_header_footer_width_values'])->toBe([6, 8, 10, 12])
        ->and(Config::$modSettings)->toHaveKey('lp_header_panel_width')
        ->and(Utils::$context['config_vars'])->not->toBeEmpty();
});

it('saves panel configuration', function () {
    $this->request->shouldReceive('has')->with('save')->andReturn(true);
    $this->request->shouldReceive('get')->with('lp_left_panel_width')->andReturn([3, 4]);
    $this->request->shouldReceive('get')->with('lp_right_panel_width')->andReturn([3]);
    $this->request->shouldReceive('get')->with('lp_panel_direction')->andReturn(['header' => 'row']);

    $post = mock(PostInterface::class);
    $post->shouldReceive('put')->with('lp_left_panel_width', json_encode([3, 4]))->once();
    $post->shouldReceive('put')->with('lp_right_panel_width', json_encode([3]))->once();
    $post->shouldReceive('put')->with('lp_panel_direction', json_encode(['header' => 'row']))->once();
    $post->shouldIgnoreMissing();
    AppMockRegistry::set(PostInterface::class, $post);

    $this->response->shouldReceive('redirect')->once()->with('action=admin;area=lp_settings;sa=panels');

    (new PanelConfig())->show();

    expect(true)->toBeTrue();
});

<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\Configs\ExtraConfig;
use LightPortal\Utils\InputFilter;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lang_locale']          = 'en_US';
    Lang::$txt['lp_extra']             = 'Extra';
    Lang::$txt['lp_comment_block_set'] = [
        0        => 'None',
        1        => 'Default',
        'custom' => 'Custom',
    ];
    Lang::$txt['lp_page_og_image_set']    = ['none' => 'None'];
    Lang::$txt['lp_sort_by_created']      = 'Created';
    Lang::$txt['lp_sort_by_created_desc'] = 'Created desc';
    Lang::$txt['lp_sort_by_updated']      = 'Updated';
    Lang::$txt['lp_sort_by_updated_desc'] = 'Updated desc';
    Lang::$txt['lp_permissions']          = [];
    Lang::$txt['lp_fa_source_title']      = 'Font Awesome';
    Lang::$txt['no']                      = 'No';
    Lang::$txt['lp_fa_source_css_cdn']    = 'CDN';
    Lang::$txt['lp_fa_source_css_local']  = 'Local';
    Lang::$txt['lp_fa_custom']            = 'Custom';
    Lang::$txt['lp_fa_kit']               = 'Kit';

    Config::$modSettings = [];

    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('has')->with('save')->andReturn(false)->byDefault();
    $this->request->shouldIgnoreMissing();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->response = mock(ResponseInterface::class);
    AppMockRegistry::set(ResponseInterface::class, $this->response);

    $this->inputFilter = mock(InputFilter::class);
});

it('shows extra settings with defaults', function () {
    (new ExtraConfig($this->inputFilter))->show();

    expect(Utils::$context['page_title'])->toBe('Extra')
        ->and(Utils::$context['post_url'])->toContain('area=lp_settings;sa=extra;save')
        ->and(Config::$modSettings['lp_num_comments_per_page'])->toBe(10)
        ->and(Config::$modSettings['lp_page_maximum_tags'])->toBe(10)
        ->and(Utils::$context['config_vars'])->not->toBeEmpty();
});

it('saves extra settings and filters url values', function () {
    $this->request->shouldReceive('has')->with('save')->andReturn(true);

    $this->inputFilter->shouldReceive('filter')->once()->andReturn([
        'lp_menu_separate_subsection_href' => 'https://example.com/custom',
        'lp_fa_custom'                     => false,
        'lp_fa_kit'                        => 'https://kit.fontawesome.com/xxx.js',
    ]);

    $post = mock(PostInterface::class);
    $post->shouldReceive('put')->with('lp_menu_separate_subsection_href', 'https://example.com/custom')->once();
    $post->shouldReceive('put')->with('lp_fa_custom', '')->once();
    $post->shouldReceive('put')->with('lp_fa_kit', 'https://kit.fontawesome.com/xxx.js')->once();
    $post->shouldIgnoreMissing();
    AppMockRegistry::set(PostInterface::class, $post);

    $this->response->shouldReceive('redirect')->once()->with('action=admin;area=lp_settings;sa=extra');

    (new ExtraConfig($this->inputFilter))->show();

    expect(true)->toBeTrue();
});

<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\Configs\FeedbackConfig;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\UI\TemplateLoader;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lang_locale'] = 'en_US';
    Lang::$txt['lp_feedback'] = 'Feedback';
    Lang::$txt['lp_thanks']   = 'Thank you';
    Lang::$txt['name']        = 'Name';
    Lang::$txt['email']       = 'Email';
    Lang::$txt['message']     = 'Message';
    Lang::$txt['post']        = 'Post';

    Utils::$context['forum_name'] = 'Test Forum';
    Utils::$context['user']       = [
        'is_admin' => false,
        'name'     => 'TestUser',
        'email'    => 'test@example.com',
        'language' => 'english',
    ];

    Config::$modSettings['lp_enabled_plugins'] = 'HelloPortal';

    $adapter = mock(PortalAdapterInterface::class);
    $adapter->shouldReceive('getTitle')->andReturn('SQLite');
    $adapter->shouldReceive('getVersion')->andReturn('3.45.0');

    $sql = mock(PortalSqlInterface::class);
    $sql->shouldReceive('getAdapter')->andReturn($adapter);
    AppMockRegistry::set(PortalSqlInterface::class, $sql);

    $request = mock(RequestInterface::class);
    $request->shouldReceive('has')->with('success')->andReturn(false)->byDefault();
    AppMockRegistry::set(RequestInterface::class, $request);

    $this->config = new FeedbackConfig();
});

it('shows the feedback form', function () {
    $this->config->show();

    expect(Utils::$context['page_title'])->toBe('Feedback')
        ->and(Utils::$context['feedback_sent'])->toBeFalse()
        ->and(Utils::$context['success_url'])->toContain('area=lp_settings;sa=feedback;success')
        ->and(TemplateLoader::getLastContent())->not->toBeEmpty();
});

it('marks feedback as sent when the success parameter is present', function () {
    AppMockRegistry::get(RequestInterface::class)
        ->shouldReceive('has')->with('success')->andReturn(true);

    $this->config->show();

    expect(Utils::$context['feedback_sent'])->toBeTrue()
        ->and(TemplateLoader::getLastContent())->not->toBeEmpty();
});

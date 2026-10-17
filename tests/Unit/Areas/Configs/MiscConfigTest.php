<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Laminas\Db\Extra\Result\ExtendedResultInterface;
use Laminas\Db\Extra\Sql\Operations\ExtendedDelete;
use Laminas\Db\Extra\Sql\Operations\ExtendedInsert;
use LightPortal\Areas\Configs\MiscConfig;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lang_locale'] = 'en_US';

    Config::$modSettings = [];

    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('has')->with('save')->andReturn(false)->byDefault();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->sql = mock(PortalSqlInterface::class);
    AppMockRegistry::set(PortalSqlInterface::class, $this->sql);

    $this->response = mock(ResponseInterface::class);
    AppMockRegistry::set(ResponseInterface::class, $this->response);
});

it('shows misc settings and adds default values', function () {
    (new MiscConfig())->show();

    expect(Utils::$context['post_url'])->toContain('area=lp_settings;sa=misc;save')
        ->and(Config::$modSettings['lp_cache_interval'])->toBe(LP_CACHE_TIME)
        ->and(Config::$modSettings['lp_portal_action'])->toBe(LP_ACTION)
        ->and(Config::$modSettings['lp_page_param'])->toBe(LP_PAGE_PARAM)
        ->and(Utils::$context['config_vars'])->not->toBeEmpty();
});

it('saves misc settings and schedules weekly cleaning', function () {
    $this->request->shouldReceive('has')->with('save')->andReturn(true);
    $this->request->shouldReceive('has')->with('lp_weekly_cleaning')->andReturn(true);

    $delete = new ExtendedDelete('background_tasks');

    $insert = mock(ExtendedInsert::class);
    $insert->shouldReceive('values')->once()->with(Mockery::on(
        fn($values) => str_contains($values['task_file'], 'Maintainer.php')
            && str_contains($values['task_class'], 'Maintainer')
    ))->andReturnSelf();

    $result = mock(ExtendedResultInterface::class);

    $this->sql->shouldReceive('delete')->once()->with('background_tasks')->andReturn($delete);
    $this->sql->shouldReceive('insert')->once()->with('background_tasks')->andReturn($insert);
    $this->sql->shouldReceive('execute')->twice()->andReturn($result);

    $this->response->shouldReceive('redirect')->once()->with('action=admin;area=lp_settings;sa=misc');

    (new MiscConfig())->show();

    expect(true)->toBeTrue();
});

it('saves misc settings without weekly cleaning', function () {
    $this->request->shouldReceive('has')->with('save')->andReturn(true);
    $this->request->shouldReceive('has')->with('lp_weekly_cleaning')->andReturn(false);

    $delete = new ExtendedDelete('background_tasks');
    $result = mock(ExtendedResultInterface::class);

    $this->sql->shouldReceive('delete')->once()->with('background_tasks')->andReturn($delete);
    $this->sql->shouldNotReceive('insert');
    $this->sql->shouldReceive('execute')->once()->andReturn($result);

    $this->response->shouldReceive('redirect')->once();

    (new MiscConfig())->show();

    expect(true)->toBeTrue();
});

<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\BlockArea;
use LightPortal\Areas\CategoryArea;
use LightPortal\Areas\Configs\BasicConfig;
use LightPortal\Areas\Configs\ExtraConfig;
use LightPortal\Areas\Configs\FeedbackConfig;
use LightPortal\Areas\Configs\MiscConfig;
use LightPortal\Areas\Configs\PanelConfig;
use LightPortal\Areas\PageArea;
use LightPortal\Areas\PluginArea;
use LightPortal\Areas\TagArea;
use LightPortal\DataHandlers\Exports\BlockExport;
use LightPortal\DataHandlers\Exports\PageExport;
use LightPortal\DataHandlers\Exports\PluginExport;
use LightPortal\DataHandlers\Exports\TagExport;
use LightPortal\DataHandlers\Imports\BlockImport;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Hooks\AdminAreas;
use LightPortal\Lists\IconList;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;

function lpAdminAreasSpy(): object
{
    return new class {
        public array $calls = [];

        public function __call(string $name, array $args): void
        {
            $this->calls[] = $name;
        }
    };
}

function lpAdminAreasPrepareSettings(): void
{
    foreach ([BasicConfig::class, ExtraConfig::class, PanelConfig::class, MiscConfig::class, FeedbackConfig::class] as $class) {
        AppMockRegistry::set($class, lpAdminAreasSpy());
    }

    $adapter = Mockery::mock(PortalAdapterInterface::class);
    $adapter->shouldReceive('getTitle')->andReturn('SQLite');
    $adapter->shouldReceive('getVersion')->andReturn('3.45.0');

    $sql = Mockery::mock(PortalSqlInterface::class);
    $sql->shouldReceive('getAdapter')->andReturn($adapter);

    AppMockRegistry::set(PortalSqlInterface::class, $sql);
}

beforeEach(function () {
    Config::$modSettings = [
        'lp_enabled_plugins' => 'HelloPortal,Dragula',
    ];

    Utils::$context['user'] = ['is_admin' => false];
    Utils::$context['lp_quantities'] = [
        'active_blocks'     => 3,
        'active_pages'      => 5,
        'my_pages'          => 7,
        'active_categories' => 2,
        'active_tags'       => 4,
    ];
    Utils::$context['template_layers'] = ['main'];

    User::$me->permissions = ['admin_forum'];

    $GLOBALS['fatal_lang_error_calls'] = [];

    AppMockRegistry::set(IconList::class, new class {
        public function __invoke(): array
        {
            return array_fill_keys(
                [
                    'cog_spin',
                    'pager',
                    'panels',
                    'tools',
                    'comments',
                    'main',
                    'plus',
                    'export',
                    'import',
                    'packages',
                    'reports',
                    'boards',
                    'attachment',
                    'modifications',
                ],
                '<i class="fas fa-icon"></i>'
            );
        }
    });

    $this->request = Mockery::mock(RequestInterface::class);
    $this->request->shouldReceive('is')->with('printpage')->andReturn(false)->byDefault();
    $this->request->shouldReceive('sa')->with('uninstall2')->andReturn(false)->byDefault();
    $this->request->shouldReceive('get')->with('area')->andReturn('lp_settings')->byDefault();
    $this->request->shouldReceive('get')->with('sa')->andReturn(null)->byDefault();
    $this->request->shouldReceive('has')->with('u')->andReturn(false)->byDefault();
    $this->request->shouldReceive('isNot')->with('lp_pages', 'area')->andReturn(true)->byDefault();
    $this->request->shouldIgnoreMissing();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->dispatcher = Mockery::mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->byDefault();

    $this->hook = new AdminAreas($this->dispatcher);
});

it('registers the portal section after layout', function () {
    $areas = [
        'layout'  => ['label' => 'Layout'],
        'profile' => ['label' => 'Profile'],
    ];

    ($this->hook)($areas);

    expect(array_keys($areas))->toBe(['layout', 'lp_portal', 'profile'])
        ->and($areas['lp_portal']['permission'])->toBe([
            'admin_forum',
            'light_portal_manage_pages_any',
            'light_portal_manage_pages_own',
        ])
        ->and(array_keys($areas['lp_portal']['areas']))->toBe([
            'lp_settings',
            'lp_blocks',
            'lp_pages',
            'lp_categories',
            'lp_tags',
            'lp_plugins',
        ])
        ->and(array_keys($areas['lp_portal']['areas']['lp_settings']['subsections']))->toBe([
            'basic',
            'extra',
            'panels',
            'misc',
            'feedback',
        ])
        ->and(array_keys($areas['lp_portal']['areas']['lp_blocks']['subsections']))->toBe(['main', 'add'])
        ->and($areas['lp_portal']['areas']['lp_blocks']['amt'])->toBe(3)
        ->and($areas['lp_portal']['areas']['lp_pages']['amt'])->toBe(5)
        ->and($areas['lp_portal']['areas']['lp_categories']['amt'])->toBe(2)
        ->and($areas['lp_portal']['areas']['lp_tags']['amt'])->toBe(4)
        ->and($areas['lp_portal']['areas']['lp_plugins']['amt'])->toBe(2);
});

it('adds export and import subsections for admins', function () {
    Utils::$context['user']['is_admin'] = true;

    $areas = ['layout' => []];

    ($this->hook)($areas);

    $portalAreas = $areas['lp_portal']['areas'];

    expect(array_keys($portalAreas['lp_blocks']['subsections']))->toBe(['main', 'add', 'export', 'import'])
        ->and(array_keys($portalAreas['lp_pages']['subsections']))->toBe(['main', 'add', 'export', 'import'])
        ->and(array_keys($portalAreas['lp_categories']['subsections']))->toBe(['main', 'add', 'export', 'import'])
        ->and(array_keys($portalAreas['lp_tags']['subsections']))->toBe(['main', 'add', 'export', 'import']);

    if (extension_loaded('zip')) {
        expect(array_keys($portalAreas['lp_plugins']['subsections']))->toBe(['main', 'export', 'import']);
    }
});

it('shows own pages count for users without manage-any permission', function () {
    User::$me->permissions = ['light_portal_manage_pages_own'];
    $this->request->shouldReceive('has')->with('u')->andReturn(true);

    $areas = ['layout' => []];

    ($this->hook)($areas);

    expect($areas['lp_portal']['areas']['lp_pages']['amt'])->toBe(7);
});

it('dispatches the extendAdminAreas event', function () {
    $this->dispatcher->shouldReceive('dispatch')
        ->once()
        ->with(PortalHook::extendAdminAreas, Mockery::on(fn($params) => isset($params['areas']['lp_settings'])));

    $areas = ['layout' => []];

    ($this->hook)($areas);
});

it('does nothing when the portal cannot be loaded', function () {
    Utils::$context['uninstalling'] = true;

    $areas = ['layout' => [], 'profile' => []];

    ($this->hook)($areas);

    expect($areas)->not->toHaveKey('lp_portal')
        ->and(Config::$modSettings['minimize_files'])->toBe(0);
});

it('denies access to portal areas for non-admins', function () {
    User::$me->permissions = ['manage_boards'];

    $areas = ['layout' => []];

    ($this->hook)($areas);

    expect($GLOBALS['fatal_lang_error_calls'])->toContain('no_access');
});

it('allows access to portal areas for admins', function () {
    $areas = ['layout' => []];

    ($this->hook)($areas);

    expect($GLOBALS['fatal_lang_error_calls'])->toBeEmpty();
});

it('opens the basic settings tab by default', function () {
    lpAdminAreasPrepareSettings();

    $this->hook->settingAreas();

    expect(AppMockRegistry::get(BasicConfig::class)->calls)->toBe(['show'])
        ->and(AppMockRegistry::get(ExtraConfig::class)->calls)->toBe([])
        ->and(Utils::$context['sub_template'])->toBe('show_settings')
        ->and(Utils::$context['sub_action'])->toBe('basic')
        ->and(Utils::$context['admin']['tab_data']['title'])->toBe(LP_NAME)
        ->and(array_keys(Utils::$context['admin']['tab_data']['tabs']))->toBe([
            'basic',
            'extra',
            'panels',
            'misc',
            'feedback',
        ]);
});

it('opens the requested settings tab', function () {
    lpAdminAreasPrepareSettings();

    $this->request->shouldReceive('get')->with('sa')->andReturn('extra');

    $this->hook->settingAreas();

    expect(AppMockRegistry::get(BasicConfig::class)->calls)->toBe([])
        ->and(AppMockRegistry::get(ExtraConfig::class)->calls)->toBe(['show'])
        ->and(Utils::$context['sub_action'])->toBe('extra');
});

it('falls back to the basic tab for unknown actions', function () {
    lpAdminAreasPrepareSettings();

    $this->request->shouldReceive('get')->with('sa')->andReturn('unknown');

    $this->hook->settingAreas();

    expect(AppMockRegistry::get(BasicConfig::class)->calls)->toBe(['show'])
        ->and(Utils::$context['sub_action'])->toBe('basic');
});

it('opens the block export area and dispatches extendBlockAreas', function () {
    $blockSpy = lpAdminAreasSpy();
    $exportSpy = lpAdminAreasSpy();
    $importSpy = lpAdminAreasSpy();

    AppMockRegistry::set(BlockArea::class, $blockSpy);
    AppMockRegistry::set(BlockExport::class, $exportSpy);
    AppMockRegistry::set(BlockImport::class, $importSpy);

    $this->request->shouldReceive('get')->with('sa')->andReturn('export');
    $this->dispatcher->shouldReceive('dispatch')
        ->once()
        ->with(PortalHook::extendBlockAreas, Mockery::type('array'));

    $this->hook->blockAreas();

    expect($exportSpy->calls)->toBe(['main'])
        ->and($blockSpy->calls)->toBe([])
        ->and(Utils::$context['sub_action'])->toBe('export');
});

it('opens the page export area', function () {
    $pageSpy = lpAdminAreasSpy();
    $exportSpy = lpAdminAreasSpy();

    AppMockRegistry::set(PageArea::class, $pageSpy);
    AppMockRegistry::set(PageExport::class, $exportSpy);

    $this->request->shouldReceive('get')->with('sa')->andReturn('export');

    $this->hook->pageAreas();

    expect($exportSpy->calls)->toBe(['main'])
        ->and($pageSpy->calls)->toBe([])
        ->and(Utils::$context['sub_action'])->toBe('export');
});

it('opens category, tag and plugin areas', function () {
    $categorySpy = lpAdminAreasSpy();
    $tagSpy = lpAdminAreasSpy();
    $pluginSpy = lpAdminAreasSpy();

    AppMockRegistry::set(CategoryArea::class, $categorySpy);
    AppMockRegistry::set(TagArea::class, $tagSpy);
    AppMockRegistry::set(PluginArea::class, $pluginSpy);

    $this->hook->categoryAreas();
    expect($categorySpy->calls)->toBe(['main']);

    $this->request->shouldReceive('get')->with('sa')->andReturn('edit');

    $this->hook->tagAreas();
    expect($tagSpy->calls)->toBe(['edit'])
        ->and(Utils::$context['sub_action'])->toBe('edit');

    $this->hook->pluginAreas();
    expect($pluginSpy->calls)->toBe(['main'])
        ->and(Utils::$context['sub_action'])->toBe('main');
});

it('shows the docs link for portal areas', function () {
    AppMockRegistry::set(BlockArea::class, lpAdminAreasSpy());
    AppMockRegistry::set(BlockExport::class, lpAdminAreasSpy());
    AppMockRegistry::set(BlockImport::class, lpAdminAreasSpy());

    $this->hook->blockAreas();

    expect(Utils::$context['lp_reference_links'][0]['href'])->toContain('https://dragomano.github.io/Light-Portal/')
        ->and(Utils::$context['template_layers'])->toContain('lp_docs');
});

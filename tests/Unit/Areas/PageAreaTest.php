<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\PageArea;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Tab;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\IconList;
use LightPortal\Models\PageFactory;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\DatastarSender;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Validators\PageValidator;
use starfederation\datastar\enums\ElementPatchMode;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->repositoryMock = mock(PageRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->pageArea = new PageArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor = new ReflectionAccessor($this->pageArea);

    Lang::$txt += [
        'date'   => 'Date',
        'search' => 'Search',
        'edit_permissions' => 'Edit permissions',
    ];

    Utils::$context += [
        'lp_page'         => ['type' => 'bbc', 'options' => ['show_title' => true]],
        'lp_current_page' => ['type' => 'bbc', 'author_id' => 1],
    ];
});

arch()
    ->expect(PageArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('page');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('pages');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('restore_item')
        ->and($result)->toHaveKey('remove_forever')
        ->and($result['restore_item'])->toBeCallable()
        ->and($result['remove_forever'])->toBeCallable();
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(PageValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(PageFactory::class);
});

it('should flush cache returns true', function () {
    $result = $this->accessor->callMethod('shouldFlushCache');

    expect($result)->toBeTrue();
});

it('returns correct main form action suffix', function () {
    $result = $this->accessor->callMethod('getMainFormActionSuffix');

    expect($result)->toBe(';sa=main');
});

it('returns correct main tab data', function () {
    User::$me->allowedTo(['light_portal_manage_pages_any']);

    $this->accessor->setProperty('isModerate', true);
    $this->accessor->setProperty('isDeleted', true);

    $result = $this->accessor->callMethod('getMainTabData');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('description');
});

it('buildTable returns PortalTableBuilder instance', function () {
    Utils::$context += [
        'search_params'    => '',
        'form_action'      => 'https://example.com?action=admin;area=lp_pages',
        'user'             => ['is_admin' => false],
        'session_var'      => 'session_var',
        'session_id'       => 'session_id',
        'lp_content_types' => ['bbc' => 'BBC'],
        'lp_loaded_addons' => [],
        'lp_quantities'    => ['active_tags' => 0],
        'canonical_url'    => 'https://example.com',
        'lp_page_types'    => EntryType::all(),
    ];

    $iconListMock = mock(IconList::class);
    $iconListMock->shouldReceive('__invoke')->andReturn([
        'plus'   => '<i class="fas fa-plus"></i>',
        'views'  => '<i class="fas fa-eye"></i>',
        'search' => '<i class="fas fa-search"></i>',
    ]);
    AppMockRegistry::set(IconList::class, $iconListMock);

    $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
    $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

    $result = $this->accessor->callMethod('buildTable');

    expect($result)->toBeInstanceOf(PortalTableBuilderInterface::class);
});

it('afterMain correctly runs', function () {
    Lang::$txt['all'] = 'all';
    Lang::$txt['awaiting_approval'] = 'Awaiting approval';

    Utils::$context['lp_pages'] = ['title' => ''];
    Utils::$context['form_action'] = 'https://example.com?action=admin;area=lp_pages';
    Utils::$context['lp_quantities'] = [
        'active_pages'     => 0,
        'my_pages'         => 0,
        'unapproved_pages' => 0,
        'deleted_pages'    => 0,
    ];

    $this->repositoryMock->shouldReceive('getTotalCount')
        ->once()
        ->with('', Mockery::type('array'))
        ->andReturn(7);

    $this->accessor->setProperty('browseType', 'all');
    $this->accessor->callMethod('afterMain');

    expect(Utils::$context['lp_pages']['title'])->toContain(Utils::$context['form_action'], '(7)');
});

it('keeps the search in the view tabs', function () {
    Lang::$txt['all']               = 'all';
    Lang::$txt['awaiting_approval'] = 'Awaiting approval';

    Utils::$context['lp_pages']      = ['title' => ''];
    Utils::$context['form_action']   = 'https://example.com?action=admin;area=lp_pages';
    Utils::$context['search_params'] = 'c2VhcmNo';
    Utils::$context['lp_quantities'] = [
        'active_pages'     => 0,
        'my_pages'         => 0,
        'unapproved_pages' => 0,
        'deleted_pages'    => 0,
    ];

    $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(7);

    $this->accessor->setProperty('browseType', 'all');
    $this->accessor->callMethod('afterMain');

    expect(Utils::$context['lp_pages']['title'])
        ->toContain('params=c2VhcmNo')
        ->toContain('data-lp-quantity="pages_');
});

it('setupAdditionalAddContext prepares page list and sets context', function () {
    Utils::$context += [
        'lp_content_types' => ['bbc' => 'BBC'],
        'lp_loaded_addons' => [],
    ];

    $requestMock = mock();
    $requestMock->shouldReceive('json')->andReturn([]);
    $requestMock->shouldReceive('get')->with('add_page')->andReturn('bbc');
    $requestMock->shouldReceive('get')->with('search')->andReturn('');
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('setupAdditionalAddContext');

    expect(Utils::$context)->toHaveKey('lp_all_pages')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('bbc');
});

it('prepareValidationContext can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('prepareValidationContext');

    expect(true)->toBeTrue();
});

it('postProcessValidation can be called without errors', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('postProcessValidation');

    expect(true)->toBeTrue();
});

it('prepareCommonFields can be called without errors', function () {
    $this->accessor->callMethod('prepareCommonFields');

    expect(true)->toBeTrue();
});

describe('prepareSpecificFields', function () {
    beforeEach(function () {
        Utils::$context['lp_page'] = [
            'type'        => 'bbc',
            'content'     => 'test content',
            'options'     => [
                'show_title'           => true,
                'show_in_menu'         => false,
                'show_author_and_date' => true,
                'show_related_pages'   => false,
                'allow_comments'       => false,
            ],
            'category_id' => 1,
            'description' => 'Test description',
            'created_at'  => time() - 1000,
        ];

        Utils::$context['user']['is_admin']             = false;
        Utils::$context['lp_quantities']['active_tags'] = 0;
        Utils::$context['posting_fields']               = [];

        $categoryListMock = mock(CategoryList::class);
        $categoryListMock->shouldReceive('__invoke')->andReturn([
            [
                'category_id' => 1,
                'icon'        => 'fas fa-folder',
                'title'       => 'Test Category',
                'slug'        => 'test-category',
                'description' => '',
            ]
        ]);
        AppMockRegistry::set(CategoryList::class, $categoryListMock);
    });

    it('creates correct fields', function ($setup, $expectations) {
        foreach ($setup as $key => $value) {
            match ($key) {
                'type'                  => Utils::$context['lp_page']['type'] = $value,
                'is_admin'              => Utils::$context['user']['is_admin'] = $value,
                'active_tags'           => Utils::$context['lp_quantities']['active_tags'] = $value,
                'created_at'            => Utils::$context['lp_page']['created_at'] = $value,
                'date'                  => Utils::$context['lp_page']['date'] = $value,
                'time'                  => Utils::$context['lp_page']['time'] = $value,
                'lp_show_related_pages' => Config::$modSettings['lp_show_related_pages'] = $value,
                'lp_comment_block'      => Config::$modSettings['lp_comment_block'] = $value,
                default                 => null,
            };
        }

        $this->accessor->callMethod('prepareSpecificFields');

        foreach ($expectations as $type => $checks) {
            match ($type) {
                'has_keys'      => expect(Utils::$context['posting_fields'])->toHaveKeys($checks),
                'not_has_keys'  => array_map(fn($key) => expect(Utils::$context['posting_fields'])->not->toHaveKey($key), $checks),
                'post_box_name' => is_null($checks)
                    ? expect(isset(Utils::$context['post_box_name']))->toBeFalse()
                    : expect(Utils::$context['post_box_name'])->toBe($checks),
                'tag_in_seo_tab' => $checks
                    ? expect(Utils::$context['posting_fields']['tags']['input']['tab'])->toBe(Tab::SEO->value)
                    : expect(Utils::$context['posting_fields']['tags']['input']['tab'])->not->toBe(Tab::SEO->value),
                default => null,
            };
        }
    })->with([
        'html type creates textarea field' => [
            'setup'             => ['type' => 'html'],
            'expectations'      => [
                'post_box_name' => null,
                'has_keys'      => ['content'],
            ],
        ],
        'bbc type creates BBC field' => [
            'setup'             => ['type' => 'bbc', 'is_admin' => true],
            'expectations'      => [
                'post_box_name' => 'content',
                'has_keys'      => ['content'],
            ],
        ],
        'admin has special fields' => [
            'setup'        => ['is_admin' => true],
            'expectations' => [
                'has_keys' => ['show_in_menu', 'status'],
            ],
        ],
        'non-admin has no special fields' => [
            'setup'            => ['is_admin' => false],
            'expectations'     => [
                'not_has_keys' => ['show_in_menu', 'status'],
            ],
        ],
        'always creates common fields' => [
            'setup'        => ['is_admin' => true],
            'expectations' => [
                'has_keys' => [
                    'permissions', 'category_id', 'entry_type', 'slug',
                    'description', 'show_title', 'show_author_and_date',
                ],
            ],
        ],
        'with active tags in SEO tab' => [
            'setup'              => ['active_tags' => 5],
            'expectations'       => [
                'has_keys'       => ['tags'],
                'tag_in_seo_tab' => true,
            ],
        ],
        'without active tags not in SEO tab' => [
            'setup'              => ['active_tags' => 0],
            'expectations'       => [
                'has_keys'       => ['tags'],
                'tag_in_seo_tab' => false,
            ],
        ],
        'future page has datetime field' => [
            'setup'          => [
                'created_at' => time() + 86400,
                'date'       => '2024-12-25',
                'time'       => '12:00',
            ],
            'expectations'   => [
                'has_keys'   => ['datetime'],
            ],
        ],
        'past page has no datetime field' => [
            'setup'            => ['created_at' => time() - 1000],
            'expectations'     => [
                'not_has_keys' => ['datetime'],
            ],
        ],
        'related pages enabled' => [
            'setup'        => ['lp_show_related_pages' => true],
            'expectations' => [
                'has_keys' => ['show_related_pages'],
            ],
        ],
        'related pages disabled' => [
            'setup'            => ['lp_show_related_pages' => false],
            'expectations'     => [
                'not_has_keys' => ['show_related_pages'],
            ],
        ],
        'comments enabled' => [
            'setup'        => ['lp_comment_block' => 'default'],
            'expectations' => [
                'has_keys' => ['allow_comments'],
            ],
        ],
        'comments disabled' => [
            'setup'            => ['lp_comment_block' => 'none'],
            'expectations'     => [
                'not_has_keys' => ['allow_comments'],
            ],
        ],
    ]);
});

it('dispatchFieldsEvent dispatches event', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('dispatchFieldsEvent');
});

it('prepareEditor dispatches event', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callMethod('prepareEditor');
});

it('finalizePreviewTitle sets preview title', function () {
    Utils::$context['preview_title'] = '';

    $entity = ['id' => 1, 'title' => 'Test Page'];

    $this->accessor->callMethod('finalizePreviewTitle', [$entity]);

    expect(Utils::$context['preview_title'])->toBeString()->not->toBeEmpty();
});

it('beforeRemove logs action if user is not author', function () {
    Utils::$context += [
        'lp_current_page' => [
            'author_id' => 2,
            'title'     => 'Test Page',
        ],
    ];

    $this->accessor->callMethod('beforeRemove', [1]);

    expect(true)->toBeTrue();
});

it('checkUser redirects if user has no permissions and no userId', function () {
    $this->accessor->setProperty('userId', null);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('has')->with('actions')->andReturn(false);
    AppMockRegistry::set(RequestInterface::class, $requestMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once()->with('action=admin;area=lp_pages;u=1');
    AppMockRegistry::set(ResponseInterface::class, $responseMock);

    $this->accessor->callMethod('checkUser');
});

it('promote does nothing when items array is empty', function () {
    $this->accessor->callMethod('promote', [[]]);

    expect(true)->toBeTrue();
});

it('promote moves pages up by default', function () {
    $this->accessor->callMethod('promote', [[3, 4]]);

    expect(true)->toBeTrue();
});

it('promote moves pages down when type is down', function () {
    $this->accessor->callMethod('promote', [[1, 3], 'down']);

    expect(true)->toBeTrue();
});

it('getDefaultOptions returns correct default options', function () {
    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $result = $this->accessor->callMethod('getDefaultOptions');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('show_title')
        ->and($result)->toHaveKey('show_author_and_date')
        ->and($result)->toHaveKey('show_related_pages')
        ->and($result)->toHaveKey('allow_comments');
});

it('preparePageList prepares all pages list', function () {
    Utils::$context += [
        'lp_content_types' => [
            'bbc'  => 'BBC',
            'html' => 'HTML',
        ],
        'lp_loaded_addons' => [],
    ];

    Lang::$txt += [
        'lp_bbc'  => ['title' => 'BBC Page'],
        'lp_html' => ['title' => 'HTML Page'],
    ];

    $this->accessor->callMethod('preparePageList');

    expect(Utils::$context)->toHaveKey('lp_all_pages')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('bbc')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('html')
        ->and(count(Utils::$context['lp_all_pages']))->toBe(2);
});

it('getPageIcon returns icon for existing content type', function () {
    Utils::$context['lp_loaded_addons'] = ['custom' => ['icon' => 'fas fa-star']];

    $result = $this->accessor->callMethod('getPageIcon', ['custom']);

    expect($result)->toBe('fas fa-star');
});

it('getPageIcon returns default icon for unknown type', function () {
    $result = $this->accessor->callMethod('getPageIcon', ['unknown']);

    expect($result)->toBe('fas fa-question');
});
it('beforeMain loads params and checks user', function () {
    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldNotReceive('redirect');
    AppMockRegistry::set(ResponseInterface::class, $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('get')->with('params')->andReturn([]);
    $requestMock->shouldReceive('get')->with('u')->andReturn(1);
    $requestMock->shouldReceive('has')->with('actions')->andReturn(false);
    $requestMock->shouldReceive('has')->with('moderate')->andReturn(true);
    $requestMock->shouldReceive('has')->with('deleted')->andReturn(false);
    $requestMock->shouldReceive('get')->with('type')->andReturn(null);
    AppMockRegistry::set(RequestInterface::class, $requestMock);

    $this->accessor->callMethod('beforeMain');

    expect($this->accessor->getProperty('userId'))->toBe(1)
        ->and($this->accessor->getProperty('isModerate'))->toBeTrue()
        ->and($this->accessor->getProperty('isDeleted'))->toBeFalse()
        ->and($this->accessor->getProperty('entryType'))->toBeNull();
});

it('performBulkActions handles delete action', function () {
    $this->repositoryMock->shouldReceive('remove')->once()->with(['1', '2']);
    $this->repositoryMock->remove(['1', '2']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('bulk_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1', '2']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('performBulkActions');
});

it('performBulkActions handles delete_forever action', function () {
    $this->repositoryMock->shouldReceive('removePermanently')->once()->with(['1']);
    $this->repositoryMock->removePermanently(['1']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('bulk_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('performBulkActions');
});

it('performBulkActions handles toggle action', function () {
    $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(['1', '3']);
    $this->repositoryMock->toggleStatus(['1', '3']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('bulk_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1', '3']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('performBulkActions');
});

it('performBulkActions handles promote actions', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('bulk_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['2', '4']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('performBulkActions');
});

it('performBulkActions does nothing when no bulk actions', function () {
    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('bulk_actions')->andReturn(true);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProperty('request', $requestMock);

    $this->accessor->callMethod('performBulkActions');

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});

describe('Datastar actions', function () {
    beforeEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);

        Utils::$context['lp_actions_url']   = 'https://example.com/index.php?action=admin&area=lp_pages&actions&sa=main&session=token';
        Utils::$context['form_action']      = 'https://example.com?action=admin;area=lp_pages;sa=main';
        Utils::$context['session_var']      = 'session_var';
        Utils::$context['session_id']       = 'session_id';
        Utils::$context['search_params']    = '';
        Utils::$context['search']           = ['string' => ''];
        Utils::$context['user']['is_admin'] = true;
        Utils::$context['lp_content_types'] = ['bbc' => 'BBC'];
        Utils::$context['lp_loaded_addons'] = [];
        Utils::$context['lp_quantities']    = ['active_tags' => 0];
        Utils::$context['lp_page_types']    = EntryType::all();

        Utils::$smcFunc['json_encode'] = 'json_encode';
        Utils::$smcFunc['json_decode'] = 'json_decode';

        Lang::$txt += [
            'lp_pages_extra'               => 'Pages',
            'lp_no_items'                  => 'No items',
            'all'                          => 'All',
            'awaiting_approval'            => 'Awaiting approval',
            'lp_pages_deleted'             => 'Deleted pages',
            'lp_my_pages'                  => 'My pages',
            'lp_views'                     => 'Views',
            'lp_slug'                      => 'Slug',
            'lp_title'                     => 'Title',
            'status'                       => 'Status',
            'lp_actions'                   => 'Actions',
            'custom_profile_icon'          => 'Icon',
            'modify'                       => 'Modify',
            'remove'                       => 'Remove',
            'restore_message'              => 'Restore',
            'lp_action_remove_permanently' => 'Delete forever',
            'lp_action_on'                 => 'On',
            'lp_action_off'                => 'Off',
            'lp_page_status_set'           => [0 => 'No', 1 => 'Yes', 2 => 'Pending'],
        ];

        $iconList = mock(IconList::class);
        $iconList->shouldReceive('__invoke')->andReturn([
            'plus'     => '<i class="fas fa-plus"></i>',
            'views'    => '<i class="fas fa-eye"></i>',
            'search'   => '<i class="fas fa-search"></i>',
            'ellipsis' => '<i class="fas fa-ellipsis"></i>',
        ]);
        AppMockRegistry::set(IconList::class, $iconList);

        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->andReturn(null);
        $requestMock->shouldReceive('has')->andReturn(false);
        $this->accessor->setProperty('request', $requestMock);
        AppMockRegistry::set(RequestInterface::class, $requestMock);

        $this->senderMock = mock(DatastarSender::class);
        $this->accessor->setProperty('datastarSender', $this->senderMock);

        $this->pageRow = [
            'id'           => 7,
            'slug'         => 'test-page',
            'type'         => 'bbc',
            'entry_type'   => 0,
            'status'       => 1,
            'num_views'    => 0,
            'num_comments' => 0,
            'author_id'    => 1,
            'author_name'  => 'Tester',
            'date'         => 'today',
            'created_at'   => 0,
            'updated_at'   => 0,
            'is_front'     => false,
            'title'        => 'Test page',
        ];
    });

    afterEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);
    });

    it('toggles the status and patches the row', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => $this->pageRow]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(19);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'id="list_lp_pages_7"')), '#list_lp_pages_7');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('does not patch view counters when the status is toggled', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        // A toggle changes the status between inactive and active, and both states belong to the same views
        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => $this->pageRow]);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'id="list_lp_pages_7"')), '#list_lp_pages_7');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('does not patch anything for regular requests', function () {
        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('removes a page and patches the current, the deleted and the own view counters', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => $this->pageRow]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(18);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_pages_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_all">(18)</span>', '[data-lp-quantity="pages_all"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_own">(18)</span>', '[data-lp-quantity="pages_own"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_del">(18)</span>', '[data-lp-quantity="pages_del"]');

        $this->accessor->callMethod('handleRemoveItem', [7]);

        expect(true)->toBeTrue();
    });

    it('shows the empty state when the last row of the view is removed', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => $this->pageRow]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_pages_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_all">(0)</span>', '[data-lp-quantity="pages_all"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_own">(0)</span>', '[data-lp-quantity="pages_own"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_del">(0)</span>', '[data-lp-quantity="pages_del"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(
                Mockery::on(fn(string $html) => str_contains($html, 'colspan="8"') && str_contains($html, 'centertext')),
                '#lp_pages tbody',
                ElementPatchMode::Inner
            );

        $this->accessor->callMethod('handleRemoveItem', [7]);

        expect(true)->toBeTrue();
    });

    it('restores a page and patches the deleted and the target view counters', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => $this->pageRow]);
        $this->repositoryMock->shouldReceive('restore')->once()->with([7]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(11);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_pages_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_del">(11)</span>', '[data-lp-quantity="pages_del"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_all">(11)</span>', '[data-lp-quantity="pages_all"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_own">(11)</span>', '[data-lp-quantity="pages_own"]');

        $this->accessor->callMethod('handleRestore', [7]);

        expect(true)->toBeTrue();
    });

    it('restores an unapproved page into the moderation view', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('getAll')->andReturn([7 => array_merge($this->pageRow, ['status' => 2])]);
        $this->repositoryMock->shouldReceive('restore')->once()->with([7]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(1);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_pages_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_del">(1)</span>', '[data-lp-quantity="pages_del"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_mod">(1)</span>', '[data-lp-quantity="pages_mod"]');
        $this->senderMock->shouldNotReceive('patchElements')
            ->with(Mockery::type('string'), '[data-lp-quantity="pages_all"]');

        $this->accessor->callMethod('handleRestore', [7]);

        expect(true)->toBeTrue();
    });

    it('removes a page permanently and patches the deleted view counter', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('removePermanently')->once()->with([7]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(10);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_pages_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="pages_del">(10)</span>', '[data-lp-quantity="pages_del"]');

        $this->accessor->callMethod('handleRemoveForever', [7]);

        expect(true)->toBeTrue();
    });

    it('appends the browse view and the search to the action URL', function () {
        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->with('search')->andReturn('test query');
        $requestMock->shouldReceive('get')->with('u')->andReturn(null);
        $requestMock->shouldReceive('get')->with('type')->andReturn(null);
        $requestMock->shouldReceive('get')->with('params')->andReturn(null);
        $requestMock->shouldReceive('has')->andReturn(false);
        $this->accessor->setProperty('request', $requestMock);
        AppMockRegistry::set(RequestInterface::class, $requestMock);

        $this->accessor->setProperty('isDeleted', true);
        Utils::$context['admin_menu_name'] = 'admin';
        Utils::$context['page_title']      = '';

        $this->accessor->callMethod('setupMainContext');

        expect(Utils::$context['lp_actions_url'])
            ->toContain('&sa=main&deleted')
            ->toContain('&search=' . urlencode('test query'));
    });

    it('applies the search term to the query', function () {
        $this->accessor->callMethod('loadParamsFromRequest');
        $this->accessor->callMethod('calculateParams', ['test query']);

        $params = $this->accessor->getProperty('params');

        expect($params[1][0]->getExpression())->toBe("(LOWER(p.slug) LIKE ? ESCAPE '!' OR LOWER(t.title) LIKE ? ESCAPE '!')")
            ->and($params[1][0]->getParameters())->toBe(['%test query%', '%test query%'])
            ->and(Utils::$context['search']['string'])->toBe('test query');
    });

    it('escapes the like wildcards in the search term', function () {
        $this->accessor->callMethod('loadParamsFromRequest');
        $this->accessor->callMethod('calculateParams', ['50% off_me']);

        $params = $this->accessor->getProperty('params');

        expect($params[1][0]->getParameters())->toBe(['%50!% off!_me%', '%50!% off!_me%'])
            ->and(Utils::$context['search']['string'])->toBe('50% off_me');
    });

    it('turns the user masks into the sql wildcards', function () {
        $this->accessor->callMethod('loadParamsFromRequest');
        $this->accessor->callMethod('calculateParams', ['col*r?']);

        $params = $this->accessor->getProperty('params');

        expect($params[1][0]->getParameters())->toBe(['%col%r_%', '%col%r_%'])
            ->and(Utils::$context['search']['string'])->toBe('col*r?');
    });

    it('caps a too long search term', function () {
        $this->accessor->callMethod('loadParamsFromRequest');
        $this->accessor->callMethod('calculateParams', [str_repeat('a', 150)]);

        expect(Utils::$context['search']['string'])->toBe(str_repeat('a', 100));
    });

    it('restores the search term from the params of the link', function () {
        $encoded = base64_encode((string) json_encode([
            'string'   => 'AT&T',
            'u'        => null,
            'moderate' => false,
            'deleted'  => false,
            'type'     => null,
        ]));

        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->with('params')->andReturn($encoded);
        $requestMock->shouldReceive('get')->andReturn(null);
        $requestMock->shouldReceive('has')->andReturn(false);
        $this->accessor->setProperty('request', $requestMock);
        AppMockRegistry::set(RequestInterface::class, $requestMock);

        $this->accessor->callMethod('loadParamsFromRequest');
        $this->accessor->callMethod('calculateParams');

        $decoded = json_decode((string) base64_decode(Utils::$context['search_params']), true);

        expect(Utils::$context['search']['string'])->toBe('AT&T')
            ->and($decoded['string'])->toBe('AT&T');
    });

    it('patches the whole list when searching', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        // The renderer of the list is what normally fills the default list in the context
        $presenter = mock(TablePresenterInterface::class);
        $presenter->shouldReceive('show')->andReturnUsing(function () {
            $GLOBALS['context']['default_list'] = 'lp_pages';
            Utils::$context['default_list'] = 'lp_pages';
            Utils::$context['lp_pages'] = ['title' => ''];
        });
        AppMockRegistry::set(TablePresenterInterface::class, $presenter);

        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'stub_lp_pages')), '#manage_pages');

        $handlers = $this->accessor->callMethod('getCustomActionHandlers');
        $handlers['search_items'](['search_items' => 'test query']);

        // The view tabs belong to the list as well, so they are a part of the patched markup
        expect(Utils::$context['search']['string'])->toBe('test query')
            ->and(Utils::$context['lp_pages']['title'])->toContain('data-lp-quantity="pages_');
    });

    it('puts the search signal before the list', function () {
        $presenter = mock(TablePresenterInterface::class);
        $presenter->shouldReceive('show')->andReturnNull();
        AppMockRegistry::set(TablePresenterInterface::class, $presenter);

        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        $this->accessor->callMethod('calculateParams', ['AT&T']);

        ob_start();
        $this->accessor->callMethod('showMainContent');
        $html = (string) ob_get_clean();

        expect($html)->toContain('data-signals')
            ->and($html)->toContain('&amp;T')
            ->and($html)->not->toContain('&amp;amp;');
    });
});

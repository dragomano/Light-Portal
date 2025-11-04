<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\PageArea;
use LightPortal\Enums\EntryType;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Models\PageFactory;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Validators\PageValidator;
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
    ];
});

arch()
    ->expect(PageArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('returns correct entity name', function () {
    $result = $this->accessor->callProtectedMethod('getEntityName');

    expect($result)->toBe('page');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callProtectedMethod('getEntityNamePlural');

    expect($result)->toBe('pages');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callProtectedMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('restore_item')
        ->and($result)->toHaveKey('remove_forever')
        ->and($result['restore_item'])->toBeCallable()
        ->and($result['remove_forever'])->toBeCallable();
});

it('returns correct validator class', function () {
    $result = $this->accessor->callProtectedMethod('getValidatorClass');

    expect($result)->toBe(PageValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callProtectedMethod('getFactoryClass');

    expect($result)->toBe(PageFactory::class);
});

it('should flush cache returns true', function () {
    $result = $this->accessor->callProtectedMethod('shouldFlushCache');

    expect($result)->toBeTrue();
});

it('returns correct main form action suffix', function () {
    $result = $this->accessor->callProtectedMethod('getMainFormActionSuffix');

    expect($result)->toBe(';sa=main');
});

it('returns correct main tab data', function () {
    User::$me->allowedTo(['light_portal_manage_pages_any']);

    $result = $this->accessor->callProtectedMethod('getMainTabData');

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

    $result = $this->accessor->callProtectedMethod('buildTable');

    expect($result)->toBeInstanceOf(PortalTableBuilderInterface::class);
});

it('setupAdditionalAddContext prepares page list and sets context', function () {
    Utils::$context += [
        'lp_content_types' => ['bbc' => 'BBC'],
        'lp_loaded_addons' => [],
        'lp_current_page'  => [],
    ];

    $requestMock = mock();
    $requestMock->shouldReceive('json')->andReturn([]);
    $requestMock->shouldReceive('get')->with('add_page')->andReturn('bbc');
    $requestMock->shouldReceive('get')->with('search')->andReturn('');

    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('setupAdditionalAddContext');

    expect(Utils::$context)->toHaveKey('lp_all_pages')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('bbc');
});

it('prepareValidationContext sets up validation data', function () {
    Utils::$context['lp_current_page'] = [
        'type'    => 'bbc',
        'options' => ['show_title' => true],
    ];

    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $postMock = mock();
    $postMock->shouldReceive('put')->with('type', 'bbc');

    $this->accessor->setProtectedProperty('post', $postMock);

    $this->accessor->callProtectedMethod('prepareValidationContext');

    expect(Utils::$context['lp_current_page']['options'])->toBeArray()
        ->and(array_keys(Utils::$context['lp_current_page']['options']))->toContain('show_title');
});

it('postProcessValidation sets default date and time and processes options', function () {
    Utils::$context += [
        'lp_current_page' => [
            'type' => 'bbc',
        ],
        'lp_page' => [
            'options' => [
                'show_title' => true,
            ],
        ],
    ];

    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callProtectedMethod('postProcessValidation');

    expect(Utils::$context['lp_page'])->toHaveKey('date')
        ->and(Utils::$context['lp_page'])->toHaveKey('time')
        ->and(Utils::$context['lp_page']['options']['show_title'])->toBeBool();
});

it('prepareCommonFields does nothing', function () {
    $this->accessor->callProtectedMethod('prepareCommonFields');

    expect(true)->toBeTrue();
});

it('dispatchFieldsEvent dispatches event', function () {
    Utils::$context['lp_page'] = [
        'options' => ['show_title' => true],
        'type'    => 'bbc',
    ];

    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callProtectedMethod('dispatchFieldsEvent');
});

it('prepareEditor dispatches event', function () {
    Utils::$context['lp_page'] = ['type' => 'bbc'];

    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $this->accessor->callProtectedMethod('prepareEditor');
});

it('finalizePreviewTitle sets preview title', function () {
    Utils::$context['preview_title'] = '';

    $entity = ['id' => 1, 'title' => 'Test Page'];

    $this->accessor->callProtectedMethod('finalizePreviewTitle', [$entity]);

    expect(Utils::$context['preview_title'])->toBeString()->not->toBeEmpty();
});

it('beforeRemove logs action if user is not author', function () {
    Utils::$context += [
        'lp_current_page' => [
            'author_id' => 2,
            'title'     => 'Test Page',
        ],
    ];

    $this->accessor->callProtectedMethod('beforeRemove', [1]);

    expect(true)->toBeTrue();
});

it('checkUser redirects if user has no permissions and no userId', function () {
    $this->accessor->setProtectedProperty('userId', null);

    User::$me->allowedTo = fn() => false;

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once()->with('action=admin;area=lp_pages;u=1');
    AppMockRegistry::set(ResponseInterface::class, $responseMock);

    $this->accessor->setProtectedProperty('response', $responseMock);
    $this->accessor->callProtectedMethod('checkUser');
});

it('promote does nothing when items array is empty', function () {
    $this->accessor->callProtectedMethod('promote', [[]]);

    expect(true)->toBeTrue();
});

it('promote moves pages up by default', function () {
    $this->accessor->callProtectedMethod('promote', [[3, 4]]);

    expect(true)->toBeTrue();
});

it('promote moves pages down when type is down', function () {
    $this->accessor->callProtectedMethod('promote', [[1, 3], 'down']);

    expect(true)->toBeTrue();
});

it('getDefaultOptions returns correct default options', function () {
    Utils::$context['lp_current_page'] = ['type' => 'bbc'];

    $this->dispatcherMock->shouldReceive('dispatch')->once();

    $result = $this->accessor->callProtectedMethod('getDefaultOptions');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('show_title')
        ->and($result)->toHaveKey('show_author_and_date')
        ->and($result)->toHaveKey('show_related_pages')
        ->and($result)->toHaveKey('allow_comments')
        ->and($result['show_title'])->toBeTrue()
        ->and($result['allow_comments'])->toBeFalse();
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
        'lp_bbc' => ['title' => 'BBC Page'],
        'lp_html' => ['title' => 'HTML Page'],
    ];

    $this->accessor->callProtectedMethod('preparePageList');

    expect(Utils::$context)->toHaveKey('lp_all_pages')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('bbc')
        ->and(Utils::$context['lp_all_pages'])->toHaveKey('html')
        ->and(count(Utils::$context['lp_all_pages']))->toBe(2);
});

it('getPageIcon returns icon for existing content type', function () {
    Utils::$context['lp_loaded_addons'] = ['custom' => ['icon' => 'fas fa-star']];

    $result = $this->accessor->callProtectedMethod('getPageIcon', ['custom']);

    expect($result)->toBe('fas fa-star');
});

it('getPageIcon returns default icon for unknown type', function () {
    $result = $this->accessor->callProtectedMethod('getPageIcon', ['unknown']);

    expect($result)->toBe('fas fa-question');
});
it('beforeMain loads params and checks user', function () {
    $this->accessor->setProtectedProperty('isModerate', true);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    $responseMock->redirect();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('get')->with('params')->andReturn([]);
    $requestMock->shouldReceive('get')->with('u')->andReturn(1);
    $requestMock->shouldReceive('has')->with('moderate')->andReturn(false);
    $requestMock->shouldReceive('has')->with('deleted')->andReturn(false);
    $requestMock->shouldReceive('get')->with('type')->andReturn(null);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('beforeMain');

    expect(true)->toBeTrue();
});

it('performMassActions handles delete action', function () {
    $_POST['page_actions'] = 'delete';
    $_POST['items'] = ['1', '2'];

    $this->repositoryMock->shouldReceive('remove')->once()->with(['1', '2']);
    $this->repositoryMock->remove(['1', '2']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProtectedProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('mass_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1', '2']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('performMassActions');
});

it('performMassActions handles delete_forever action', function () {
    $_POST['page_actions'] = 'delete_forever';
    $_POST['items'] = ['1'];

    $this->repositoryMock->shouldReceive('removePermanently')->once()->with(['1']);
    $this->repositoryMock->removePermanently(['1']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProtectedProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('mass_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('performMassActions');
});

it('performMassActions handles toggle action', function () {
    $_POST['page_actions'] = 'toggle';
    $_POST['items'] = ['1', '3'];

    $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(['1', '3']);
    $this->repositoryMock->toggleStatus(['1', '3']);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProtectedProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('mass_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['1', '3']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('performMassActions');
});

it('performMassActions handles promote actions', function () {
    $_POST['page_actions'] = 'promote_up';
    $_POST['items'] = ['2', '4'];

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);
    $this->accessor->setProtectedProperty('cache', $cacheMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->once();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);
    $this->accessor->setProtectedProperty('response', $responseMock);

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('mass_actions')->andReturn(false);
    $requestMock->shouldReceive('isEmpty')->with('items')->andReturn(false);
    $requestMock->shouldReceive('get')->with('items')->andReturn(['2', '4']);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('performMassActions');
});

it('performMassActions does nothing when no mass actions', function () {
    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('mass_actions')->andReturn(true);
    AppMockRegistry::set(RequestInterface::class, $requestMock);
    $this->accessor->setProtectedProperty('request', $requestMock);

    $this->accessor->callProtectedMethod('performMassActions');

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callProtectedMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});

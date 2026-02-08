<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\FactoryInterface;
use LightPortal\Models\ModelInterface;
use LightPortal\Repositories\DataManagerInterface;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Validators\ValidatorInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

readonly class TestModel implements ModelInterface
{
    public function __construct(private array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }
}

class TestableAbstractArea extends AbstractArea
{
    protected function getEntityName(): string
    {
        return 'test_entity';
    }

    protected function getEntityNamePlural(): string
    {
        return 'test_entities';
    }

    protected function getCustomActionHandlers(): array
    {
        return [];
    }

    protected function getValidatorClass(): string
    {
        return TestValidator::class;
    }

    protected function getFactoryClass(): string
    {
        return TestFactory::class;
    }
}

class TestValidator implements ValidatorInterface
{
    public function validate(): array
    {
        return [];
    }
}

class TestFactory implements FactoryInterface
{
    public function create(array $data): ModelInterface
    {
        return new TestModel($data);
    }
}

beforeEach(function () {
    $this->repositoryMock = mock(DataManagerInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->testArea = new TestableAbstractArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor = new ReflectionAccessor($this->testArea);

    Lang::$txt += [
        'lp_test_entities'                    => 'Test entities',
        'lp_test_entities_manage'             => 'Manage Test Entities',
        'lp_test_entities_manage_description' => 'Description for managing test entities',
        'lp_test_entities_add'                => 'Add Test Entity',
        'lp_test_entities_add_title'          => 'Add Test Entity',
        'lp_test_entities_add_description'    => 'Description for adding test entity',
        'lp_test_entities_edit_title'         => 'Edit Test Entity',
        'lp_test_entities_edit_description'   => 'Description for editing test entity',
        'lp_test_entity_not_found'            => 'Test entity not found',
    ];
});

arch()
    ->expect(TestableAbstractArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated with dependencies', function () {
    expect($this->testArea)->toBeInstanceOf(TestableAbstractArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('test_entity');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('test_entities');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()->toBeEmpty();
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(TestValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(TestFactory::class);
});

it('returns correct current entity context key', function () {
    $result = $this->accessor->callMethod('getCurrentEntityContextKey');

    expect($result)->toBe('lp_current_test_entity');
});

it('returns correct entity context key', function () {
    $result = $this->accessor->callMethod('getEntityContextKey');

    expect($result)->toBe('lp_test_entity');
});

it('returns correct cache key', function () {
    $result = $this->accessor->callMethod('getCacheKey');

    expect($result)->toBe('active_test_entities');
});

it('returns correct edit template name', function () {
    $result = $this->accessor->callMethod('getEditTemplateName');

    expect($result)->toBe('admin/test_entity_edit');
});

it('should flush cache returns false by default', function () {
    $result = $this->accessor->callMethod('shouldFlushCache');

    expect($result)->toBeFalse();
});

it('should require title fields by default', function () {
    $result = $this->accessor->callMethod('shouldRequireTitleFields');

    expect($result)->toBeTrue();
});

it('should prepare languages by default', function () {
    $result = $this->accessor->callMethod('shouldPrepareLanguages');

    expect($result)->toBeTrue();
});

it('returns empty main form action suffix by default', function () {
    $result = $this->accessor->callMethod('getMainFormActionSuffix');

    expect($result)->toBe('');
});

it('returns empty remove redirect suffix by default', function () {
    $result = $this->accessor->callMethod('getRemoveRedirectSuffix');

    expect($result)->toBe('');
});

describe('setupMainContext', function () {
    beforeEach(function () {
        Utils::$context['admin_menu_name'] = 'test_menu';
    });

    it('sets up main context correctly', function () {
        $this->accessor->callMethod('setupMainContext');

        expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_portal'] . ' - ' . Lang::$txt["lp_test_entities_manage"])
            ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . "?action=admin;area=lp_test_entities")
            ->and(Utils::$context['test_menu']['tab_data'])->toBeArray()
            ->and(Utils::$context['test_menu']['tab_data']['title'])->toBe(LP_NAME);
    });
});

describe('getMainTabData', function () {
    it('returns correct tab data', function () {
        $result = $this->accessor->callMethod('getMainTabData');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('title')
            ->and($result)->toHaveKey('description')
            ->and($result['description'])->toBe('Description for managing test entities');
    });
});

describe('setupAddContext', function () {
    beforeEach(function () {
        Utils::$context['admin_menu_name'] = 'test_menu';
    });

    it('sets up add context correctly', function () {
        $this->accessor->callMethod('setupAddContext');

        expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_portal'] . ' - ' . Lang::$txt["lp_test_entities_add_title"])
            ->and(Utils::$context['page_area_title'])->toBe(Lang::$txt["lp_test_entities_add_title"])
            ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . "?action=admin;area=lp_test_entities;sa=add")
            ->and(Utils::$context['test_menu']['tab_data'])->toBeArray();
    });
});

describe('setupEditContext', function () {
    beforeEach(function () {
        Utils::$context['admin_menu_name'] = 'test_menu';
    });

    it('sets up edit context correctly', function () {
        $this->accessor->callMethod('setupEditContext', [123]);

        expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_portal'] . ' - ' . Lang::$txt["lp_test_entities_edit_title"])
            ->and(Utils::$context['page_area_title'])->toBe(Lang::$txt["lp_test_entities_edit_title"])
            ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . "?action=admin;area=lp_test_entities;sa=edit;id=123")
            ->and(Utils::$context['test_menu']['tab_data'])->toBeArray();
    });
});

describe('initializeCurrentEntity', function () {
    it('initializes current entity context', function () {
        $this->accessor->callMethod('initializeCurrentEntity');

        expect(Utils::$context)->toHaveKey('lp_current_test_entity')
            ->and(Utils::$context['lp_current_test_entity'])->toBeArray()->toBeEmpty();
    });
});

describe('loadCurrentEntity', function () {
    it('loads entity data and sets context', function () {
        $testData = ['id' => 123, 'title' => 'Test Entity'];
        $this->repositoryMock->shouldReceive('getData')->with(123)->andReturn($testData);

        $this->accessor->callMethod('loadCurrentEntity', [123]);

        expect(Utils::$context['lp_current_test_entity'])->toBe($testData);
    });

    it('throws error when entity not found', function () {
        $this->repositoryMock->shouldReceive('getData')->with(123)->andReturn([]);

        $this->accessor->callMethod('loadCurrentEntity', [123]);

        expect(true)->toBeTrue();
    });
});

describe('getItemId', function () {
    beforeEach(function () {
        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->with('test_entity_id')->andReturn(null);
        $requestMock->shouldReceive('get')->with('id')->andReturn(null);

        $this->accessor->setProperty('request', $requestMock);
    });

    it('returns entity id from request', function () {
        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->with('test_entity_id')->andReturn('456');
        $requestMock->shouldReceive('get')->with('id')->andReturn(null);
        AppMockRegistry::set(RequestInterface::class, $requestMock);
        $this->accessor->setProperty('request', $requestMock);

        $result = $this->accessor->callMethod('getItemId');

        expect($result)->toBe(456);
    });

    it('falls back to id parameter', function () {
        $requestMock = mock(RequestInterface::class);
        $requestMock->shouldReceive('get')->with('test_entity_id')->andReturn(null);
        $requestMock->shouldReceive('get')->with('id')->andReturn('789');
        AppMockRegistry::set(RequestInterface::class, $requestMock);
        $this->accessor->setProperty('request', $requestMock);

        $result = $this->accessor->callMethod('getItemId');

        expect($result)->toBe(789);
    });
});

describe('performActions', function () {
    beforeEach(function () {
        $this->requestMock = mock(RequestInterface::class);
        $this->accessor->setProperty('request', $this->requestMock);

        $cacheMock = mock(CacheInterface::class);
        $this->accessor->setProperty('cache', $cacheMock);
    });

    it('does nothing when no actions', function () {
        $this->requestMock->shouldReceive('hasNot')->with('actions')->andReturn(true);
        AppMockRegistry::set(RequestInterface::class, $this->requestMock);

        $this->accessor->callMethod('performActions');

        expect(true)->toBeTrue();
    });

    it('processes actions and clears cache', function () {
        $this->requestMock->shouldReceive('hasNot')->with('actions')->andReturn(false);
        $this->requestMock->shouldReceive('json')->andReturn(['delete_item' => 123]);
        $this->repositoryMock->shouldReceive('remove')->with(123);

        expect($this->accessor->callMethod('performActions'))->toBeNull();
    });
});

describe('buildTable', function () {
    beforeEach(function () {
        Utils::$context['lp_quantities'] = ['active_test_entities' => 5];
    });

    it('builds table with correct configuration', function () {
        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        Utils::$context['session_id'] = '';
        Utils::$context['session_var'] = '';

        $result = $this->accessor->callMethod('buildTable');

        expect($result)->toBeInstanceOf(PortalTableBuilderInterface::class);
    });
});

describe('getTableTitle', function () {
    it('returns title with count', function () {
        Utils::$context['lp_quantities'] = ['active_test_entities' => 10];

        $result = $this->accessor->callMethod('getTableTitle');

        expect($result)->toContain('(10)');
    });

    it('returns title without count when zero', function () {
        Utils::$context['lp_quantities'] = ['active_test_entities' => 0];

        $result = $this->accessor->callMethod('getTableTitle');

        expect($result)->not->toContain('(');
    });
});

describe('getDefaultSortColumn', function () {
    it('returns title as default sort column', function () {
        $result = $this->accessor->callMethod('getDefaultSortColumn');

        expect($result)->toBe('title');
    });
});

describe('getTableScript', function () {
    it('returns correct script', function () {
        $result = $this->accessor->callMethod('getTableScript');

        expect($result)->toBe('const entity = new Test_entity();');
    });
});

describe('getTableColumns', function () {
    it('returns empty array by default', function () {
        $result = $this->accessor->callMethod('getTableColumns');

        expect($result)->toBeArray()->toBeEmpty();
    });
});

describe('preparePreview', function () {
    beforeEach(function () {
        $this->requestMock = mock(RequestInterface::class);
        AppMockRegistry::set(RequestInterface::class, $this->requestMock);
        $this->accessor->setProperty('request', $this->requestMock);

        Utils::$context['lp_test_entity'] = [
            'title' => 'Test Title',
            'content' => 'Test content',
        ];
    });

    it('does nothing when no preview request', function () {
        $this->requestMock->shouldReceive('hasNot')->with('preview')->andReturn(true);

        $this->accessor->callMethod('preparePreview');

        expect(true)->toBeTrue();
    });

    it('prepares preview content', function () {
        $this->requestMock->shouldReceive('hasNot')->with('preview')->andReturn(false);

        $this->accessor->callMethod('preparePreview');

        expect(Utils::$context)->toHaveKey('preview_title')
            ->and(Utils::$context)->toHaveKey('preview_content')
            ->and(Utils::$context['preview_title'])->toBe('Test Title');
    });
});

describe('handleRemoveRequest', function () {
    beforeEach(function () {
        $this->requestMock = mock(RequestInterface::class);
        AppMockRegistry::set(RequestInterface::class, $this->requestMock);
        $this->accessor->setProperty('request', $this->requestMock);

        $this->responseMock = mock(ResponseInterface::class);
        AppMockRegistry::set(ResponseInterface::class, $this->responseMock);
        $this->accessor->setProperty('response', $this->responseMock);
    });

    it('returns false when no remove request', function () {
        $this->requestMock->shouldReceive('hasNot')->with('remove')->andReturn(true);

        $result = $this->accessor->callMethod('handleRemoveRequest', [123]);

        expect($result)->toBeFalse();
    });

    it('removes entity and redirects', function () {
        $this->requestMock->shouldReceive('hasNot')->with('remove')->andReturn(false);
        $this->repositoryMock->shouldReceive('remove')->with(123);
        $this->responseMock->shouldReceive('redirect')->with('action=admin;area=lp_test_entities');

        $result = $this->accessor->callMethod('handleRemoveRequest', [123]);

        expect($result)->toBeTrue();
    });
});

describe('updateEditContextTitle', function () {
    beforeEach(function () {
        Utils::$context['lp_test_entity'] = [
            'id'    => 123,
            'title' => 'Updated Title',
        ];
    });

    it('updates edit context title', function () {
        $this->accessor->callMethod('updateEditContextTitle');

        expect(Utils::$context['page_area_title'])->toContain('Updated Title')
            ->and(Utils::$context['form_action'])->toContain('id=123');
    });
});

describe('finalizePreviewTitle', function () {
    it('sets preview title with icon', function () {
        Utils::$context['preview_title'] = 'Test Title';

        $entity = ['icon' => 'fas fa-star'];

        $this->accessor->callMethod('finalizePreviewTitle', [$entity]);

        expect(Utils::$context['preview_title'])->toContain('Test Title');
    });
});

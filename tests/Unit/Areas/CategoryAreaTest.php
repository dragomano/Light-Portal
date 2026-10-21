<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\CategoryArea;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Models\CategoryFactory;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\DatastarSender;
use LightPortal\Utils\SessionManager;
use LightPortal\Validators\CategoryValidator;
use starfederation\datastar\enums\ElementPatchMode;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->repositoryMock = mock(CategoryRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->categoryArea = new CategoryArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor     = new ReflectionAccessor($this->categoryArea);

    Utils::$context += [
        'lp_category'   => [],
        'preview_title' => 'Test Title',
        'right_to_left' => false,
    ];
});

arch()
    ->expect(CategoryArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated', function () {
    expect($this->categoryArea)->toBeInstanceOf(CategoryArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('category');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('categories');
});

it('returns correct custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('update_priority')
        ->and($result['update_priority'])->toBeCallable();
});

it('returns correct default sort column', function () {
    $result = $this->accessor->callMethod('getDefaultSortColumn');

    expect($result)->toBe('priority');
});

it('returns correct table script', function () {
    $result = $this->accessor->callMethod('getTableScript');

    expect($result)->toBeString()
        ->and($result)->toContain('const entity = new Category();')
        ->and($result)->toContain('Sortable')
        ->and($result)->toContain('updatePriority');
});

it('returns correct table columns', function () {
    $result = $this->accessor->callMethod('getTableColumns');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(6)
        ->and($result[0])->toBeInstanceOf(IdColumn::class)
        ->and($result[1])->toBeInstanceOf(IconColumn::class)
        ->and($result[2])->toBeInstanceOf(TitleColumn::class)
        ->and($result[3])->toBeInstanceOf(Column::class)
        ->and($result[4])->toBeInstanceOf(StatusColumn::class)
        ->and($result[5])->toBeInstanceOf(ContextMenuColumn::class);
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(CategoryValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(CategoryFactory::class);
});

it('prepareSpecificFields can be called without errors', function () {
    $this->accessor->callMethod('prepareSpecificFields');

    expect(true)->toBeTrue();
});

it('finalizePreviewTitle can be called without errors', function () {
    $this->accessor->callMethod('finalizePreviewTitle', [['icon' => 'fas fa-test']]);

    expect(true)->toBeTrue();
});

it('getRepository returns correct instance', function () {
    $result = $this->accessor->callMethod('getRepository');

    expect($result)->toBe($this->repositoryMock);
});

it('parses comma-separated ids into a priority list', function () {
    $this->repositoryMock->shouldReceive('updatePriority')->once()->with([3, 1, 7]);

    $handlers = $this->accessor->callMethod('getCustomActionHandlers');

    $handlers['update_priority'](['update_priority' => '3,1,7']);

    expect(true)->toBeTrue();
});

it('keeps the order of an array payload', function () {
    $this->repositoryMock->shouldReceive('updatePriority')->once()->with([5, 2]);

    $handlers = $this->accessor->callMethod('getCustomActionHandlers');

    $handlers['update_priority'](['update_priority' => [5, 2]]);

    expect(true)->toBeTrue();
});

describe('Datastar actions', function () {
    beforeEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);

        Utils::$context['form_action']      = 'https://example.com/index.php?action=admin;area=lp_categories';
        Utils::$context['lp_actions_url']   = 'https://example.com/index.php?action=admin&area=lp_categories&actions&session=token';
        Utils::$context['session_var']      = 'session';
        Utils::$context['session_id']       = 'token';
        Utils::$context['user']['is_admin'] = true;

        Lang::$txt['lp_categories']       = 'Categories';
        Lang::$txt['lp_categories_add']   = 'Add category';
        Lang::$txt['lp_actions']          = 'Actions';
        Lang::$txt['lp_title']            = 'Title';
        Lang::$txt['lp_block_priority']   = 'Priority';
        Lang::$txt['lp_no_items']         = 'No items';
        Lang::$txt['status']              = 'Status';
        Lang::$txt['modify']              = 'Modify';
        Lang::$txt['remove']              = 'Remove';
        Lang::$txt['custom_profile_icon'] = 'Icon';
        Lang::$txt['lp_action_on']        = 'On';
        Lang::$txt['lp_action_off']       = 'Off';

        $this->senderMock = mock(DatastarSender::class);
        $this->accessor->setProperty('datastarSender', $this->senderMock);

        $this->sessionManagerMock = mock(SessionManager::class);
        $this->sessionManagerMock->shouldReceive('__invoke')->andReturn(['active_categories' => 4]);
        AppMockRegistry::set(SessionManager::class, $this->sessionManagerMock);

        $iconList = mock(IconList::class);
        $iconList->shouldReceive('__invoke')->andReturn([
            'plus'     => '<i class="fas fa-plus"></i>',
            'sort'     => '<i class="fas fa-sort"></i>',
            'ellipsis' => '<i class="fas fa-ellipsis"></i>',
        ]);
        AppMockRegistry::set(IconList::class, $iconList);
    });

    afterEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);
    });

    it('toggles the status without patching for non-Datastar requests', function () {
        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('patches the table row and the quantity for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')
            ->once()
            ->with(0, 1, 'priority', '', ['category_id = ?' => 7])
            ->andReturn([7 => [
                'id'          => 7,
                'title'       => 'Test category',
                'icon'        => '<i class="fas fa-test"></i>',
                'priority'    => 0,
                'status'      => 1,
                'description' => '',
            ]]);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'id="list_lp_categories_7"')), '#list_lp_categories_7');

        // New status is active, so the cached counter is incremented without a database query
        $this->sessionManagerMock->shouldReceive('adjust')->once()->with('active_categories', 1)->andReturn(5);
        $this->sessionManagerMock->shouldNotReceive('__invoke');

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="categories">(5)</span>', '[data-lp-quantity="categories"]');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('decrements the quantity when the toggled item becomes inactive', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(8);
        $this->repositoryMock->shouldReceive('getAll')
            ->andReturn([8 => [
                'id'          => 8,
                'title'       => 'Inactive category',
                'icon'        => '<i class="fas fa-test"></i>',
                'priority'    => 1,
                'status'      => 0,
                'description' => '',
            ]]);

        $this->senderMock->shouldReceive('patchElements')->twice();

        $this->sessionManagerMock->shouldReceive('adjust')->once()->with('active_categories', -1)->andReturn(3);

        $this->accessor->callMethod('handleToggleItem', [8]);

        expect(true)->toBeTrue();
    });

    it('recounts the quantity when the cached value is missing', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(9);
        $this->repositoryMock->shouldReceive('getAll')
            ->andReturn([9 => [
                'id'          => 9,
                'title'       => 'Test category',
                'icon'        => '<i class="fas fa-test"></i>',
                'priority'    => 2,
                'status'      => 1,
                'description' => '',
            ]]);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'id="list_lp_categories_9"')), '#list_lp_categories_9');

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="categories">(4)</span>', '[data-lp-quantity="categories"]');

        // Without a cached value the counter has to be recalculated
        $uncachedSession = mock(SessionManager::class);
        $uncachedSession->shouldReceive('adjust')->once()->andReturnNull();
        $uncachedSession->shouldReceive('__invoke')->once()->andReturn(['active_categories' => 4]);
        AppMockRegistry::set(SessionManager::class, $uncachedSession);

        $this->accessor->callMethod('handleToggleItem', [9]);

        expect(true)->toBeTrue();
    });

    it('does not patch a missing row', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(7);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggleItem', [7]);

        expect(true)->toBeTrue();
    });

    it('removes the row and updates the quantity for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(7);
        // The remaining active items make the empty state query unnecessary
        $this->repositoryMock->shouldNotReceive('getTotalCount');

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_categories_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="categories">(4)</span>', '[data-lp-quantity="categories"]');

        $this->accessor->callMethod('handleRemoveItem', [7]);

        expect(true)->toBeTrue();
    });

    it('shows the empty state when the last row is removed', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(7);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        // The last item was removed, so the counter is recalculated from scratch
        $emptySession = mock(SessionManager::class);
        $emptySession->shouldReceive('__invoke')->andReturn(['active_categories' => 0]);
        AppMockRegistry::set(SessionManager::class, $emptySession);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_categories_7');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="categories"></span>', '[data-lp-quantity="categories"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(
                '<tr class="windowbg"><td colspan="7" class="centertext">No items</td></tr>',
                '#lp_categories tbody',
                ElementPatchMode::Inner
            );

        $this->accessor->callMethod('handleRemoveItem', [7]);

        expect(true)->toBeTrue();
    });

    it('removes without Datastar patch for regular requests', function () {
        $this->repositoryMock->shouldReceive('remove')->once()->with(7);
        $this->senderMock->shouldNotReceive('removeElements');

        $this->accessor->callMethod('handleRemoveItem', [7]);

        expect(true)->toBeTrue();
    });

    it('ignores empty items', function () {
        $this->repositoryMock->shouldNotReceive('toggleStatus');
        $this->repositoryMock->shouldNotReceive('remove');

        $this->accessor->callMethod('handleToggleItem', ['']);
        $this->accessor->callMethod('handleRemoveItem', [null]);

        expect(true)->toBeTrue();
    });

    it('renders the row with all table cells', function () {
        $html = $this->accessor->callMethod('renderEntityRow', [[
            'id'          => 7,
            'title'       => 'Test category',
            'icon'        => '<i class="fas fa-test"></i>',
            'priority'    => 3,
            'status'      => 1,
            'description' => '',
        ]]);

        expect($html)
            ->toContain('<tr class="windowbg" id="list_lp_categories_7">')
            ->toContain('<td class="id" style="text-align: center">7</td>')
            ->toContain('<td class="icon centertext">')
            ->toContain('<td class="title word_break">')
            ->toContain('<td class="status centertext">')
            ->toContain('<td class="bulk centertext">')
            ->and(html_entity_decode((string) $html, ENT_QUOTES))->toContain('toggle_item: 7');
    });

    it('adds sort controls to the table row', function () {
        $builder = $this->accessor->callMethod('buildTableWithBulkActions');
        $rows    = $builder->build()['additional_rows'] ?? [];
        $sortRow = $rows[array_key_last($rows)];

        expect($sortRow['position'])->toBe('below_table_data')
            ->and($sortRow['value'])
            ->toContain('id="lp_category_sort_ids"')
            ->toContain('data-bind="sort_ids"')
            ->toContain('id="lp_category_sort_trigger"')
            ->toContain("@post('https://example.com/index.php?action=admin&area=lp_categories&actions&session=token', {payload: {update_priority: \$sort_ids}})");
    });
});

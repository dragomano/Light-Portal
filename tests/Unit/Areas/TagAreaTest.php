<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Areas\AbstractArea;
use LightPortal\Areas\AreaInterface;
use LightPortal\Areas\TagArea;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Models\TagFactory;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\DatastarSender;
use LightPortal\Utils\SessionManager;
use LightPortal\Validators\TagValidator;
use starfederation\datastar\enums\ElementPatchMode;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->repositoryMock = mock(TagRepositoryInterface::class);
    $this->dispatcherMock = mock(EventDispatcherInterface::class);

    $this->tagArea  = new TagArea($this->repositoryMock, $this->dispatcherMock);
    $this->accessor = new ReflectionAccessor($this->tagArea);
});

arch()
    ->expect(TagArea::class)
    ->toExtend(AbstractArea::class)
    ->toImplement(AreaInterface::class);

it('can be instantiated', function () {
    expect($this->tagArea)->toBeInstanceOf(TagArea::class);
});

it('returns correct entity name', function () {
    $result = $this->accessor->callMethod('getEntityName');

    expect($result)->toBe('tag');
});

it('returns correct entity name plural', function () {
    $result = $this->accessor->callMethod('getEntityNamePlural');

    expect($result)->toBe('tags');
});

it('returns empty custom action handlers', function () {
    $result = $this->accessor->callMethod('getCustomActionHandlers');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns correct table columns', function () {
    $result = $this->accessor->callMethod('getTableColumns');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(5)
        ->and($result[0])->toBeInstanceOf(IdColumn::class)
        ->and($result[1])->toBeInstanceOf(IconColumn::class)
        ->and($result[2])->toBeInstanceOf(TitleColumn::class)
        ->and($result[3])->toBeInstanceOf(StatusColumn::class)
        ->and($result[4])->toBeInstanceOf(ContextMenuColumn::class);
});

it('returns correct validator class', function () {
    $result = $this->accessor->callMethod('getValidatorClass');

    expect($result)->toBe(TagValidator::class);
});

it('returns correct factory class', function () {
    $result = $this->accessor->callMethod('getFactoryClass');

    expect($result)->toBe(TagFactory::class);
});

describe('Datastar actions', function () {
    beforeEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);

        Utils::$context['form_action']      = 'https://example.com/index.php?action=admin;area=lp_tags';
        Utils::$context['lp_actions_url']   = 'https://example.com/index.php?action=admin&area=lp_tags&actions&session=token';
        Utils::$context['session_var']      = 'session';
        Utils::$context['session_id']       = 'token';
        Utils::$context['user']['is_admin'] = true;

        Lang::$txt['lp_tags']             = 'Tags';
        Lang::$txt['lp_tags_add']         = 'Add tag';
        Lang::$txt['lp_actions']          = 'Actions';
        Lang::$txt['lp_title']            = 'Title';
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
        $this->sessionManagerMock->shouldReceive('__invoke')->andReturn(['active_tags' => 30]);
        AppMockRegistry::set(SessionManager::class, $this->sessionManagerMock);

        $iconList = mock(IconList::class);
        $iconList->shouldReceive('__invoke')->andReturn([
            'plus'     => '<i class="fas fa-plus"></i>',
            'ellipsis' => '<i class="fas fa-ellipsis"></i>',
        ]);
        AppMockRegistry::set(IconList::class, $iconList);
    });

    afterEach(function () {
        unset($_SERVER['HTTP_DATASTAR_REQUEST']);
    });

    it('toggles the status without patching for non-Datastar requests', function () {
        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(11);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggleItem', [11]);

        expect(true)->toBeTrue();
    });

    it('patches the table row and the quantity for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(11);
        $this->repositoryMock->shouldReceive('getAll')
            ->once()
            ->with(0, 1, 'title', '', ['tag_id = ?' => 11])
            ->andReturn([11 => [
                'id'     => 11,
                'title'  => 'Test tag',
                'icon'   => '<i class="fas fa-test"></i>',
                'status' => 1,
            ]]);

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(Mockery::on(fn(string $html) => str_contains($html, 'id="list_lp_tags_11"')), '#list_lp_tags_11');

        // New status is active, so the cached counter is incremented without a database query
        $this->sessionManagerMock->shouldReceive('adjust')->once()->with('active_tags', 1)->andReturn(31);
        $this->sessionManagerMock->shouldNotReceive('__invoke');

        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="tags">(31)</span>', '[data-lp-quantity="tags"]');

        $this->accessor->callMethod('handleToggleItem', [11]);

        expect(true)->toBeTrue();
    });

    it('decrements the quantity when the toggled item becomes inactive', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(12);
        $this->repositoryMock->shouldReceive('getAll')
            ->andReturn([12 => [
                'id'     => 12,
                'title'  => 'Inactive tag',
                'icon'   => '<i class="fas fa-test"></i>',
                'status' => 0,
            ]]);

        $this->senderMock->shouldReceive('patchElements')->twice();

        $this->sessionManagerMock->shouldReceive('adjust')->once()->with('active_tags', -1)->andReturn(29);

        $this->accessor->callMethod('handleToggleItem', [12]);

        expect(true)->toBeTrue();
    });

    it('does not patch a missing row', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('toggleStatus')->once()->with(11);
        $this->repositoryMock->shouldReceive('getAll')->andReturn([]);
        $this->senderMock->shouldNotReceive('patchElements');

        $this->accessor->callMethod('handleToggleItem', [11]);

        expect(true)->toBeTrue();
    });

    it('removes the row and updates the quantity for Datastar requests', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(11);
        // The remaining active items make the empty state query unnecessary
        $this->repositoryMock->shouldNotReceive('getTotalCount');

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_tags_11');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="tags">(30)</span>', '[data-lp-quantity="tags"]');

        $this->accessor->callMethod('handleRemoveItem', [11]);

        expect(true)->toBeTrue();
    });

    it('shows the empty state when the last row is removed', function () {
        $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

        $this->repositoryMock->shouldReceive('remove')->once()->with(11);
        $this->repositoryMock->shouldReceive('getTotalCount')->andReturn(0);

        // The last item was removed, so the counter is recalculated from scratch
        $emptySession = mock(SessionManager::class);
        $emptySession->shouldReceive('__invoke')->andReturn(['active_tags' => 0]);
        AppMockRegistry::set(SessionManager::class, $emptySession);

        $this->senderMock->shouldReceive('removeElements')->once()->with('#list_lp_tags_11');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with('<span data-lp-quantity="tags"></span>', '[data-lp-quantity="tags"]');
        $this->senderMock->shouldReceive('patchElements')
            ->once()
            ->with(
                '<tr class="windowbg"><td colspan="6" class="centertext">No items</td></tr>',
                '#lp_tags tbody',
                ElementPatchMode::Inner
            );

        $this->accessor->callMethod('handleRemoveItem', [11]);

        expect(true)->toBeTrue();
    });

    it('removes without Datastar patch for regular requests', function () {
        $this->repositoryMock->shouldReceive('remove')->once()->with(11);
        $this->senderMock->shouldNotReceive('removeElements');

        $this->accessor->callMethod('handleRemoveItem', [11]);

        expect(true)->toBeTrue();
    });

    it('ignores empty items', function () {
        $this->repositoryMock->shouldNotReceive('toggleStatus');
        $this->repositoryMock->shouldNotReceive('remove');

        $this->accessor->callMethod('handleToggleItem', ['']);
        $this->accessor->callMethod('handleRemoveItem', [0]);

        expect(true)->toBeTrue();
    });

    it('renders the row with all table cells', function () {
        $html = $this->accessor->callMethod('renderEntityRow', [[
            'id'     => 11,
            'title'  => 'Test tag',
            'icon'   => '<i class="fas fa-test"></i>',
            'status' => 1,
        ]]);

        expect($html)
            ->toContain('<tr class="windowbg" id="list_lp_tags_11">')
            ->toContain('<td class="id" style="text-align: center">11</td>')
            ->toContain('<td class="icon centertext">')
            ->toContain('<td class="title word_break">')
            ->toContain('<td class="status centertext">')
            ->toContain('<td class="bulk centertext">')
            ->and(html_entity_decode((string) $html, ENT_QUOTES))->toContain('toggle_item: 11');
    });

    it('uses a stable row selector', function () {
        expect($this->accessor->callMethod('getListId'))->toBe('lp_tags')
            ->and($this->accessor->callMethod('getRowId', [11]))->toBe('list_lp_tags_11')
            ->and($this->accessor->callMethod('getRowSelector', [11]))->toBe('#list_lp_tags_11');
    });
});

<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\ListInterface;
use LightPortal\UI\Partials\ActionSelect;
use LightPortal\UI\Partials\AreaSelect;
use LightPortal\UI\Partials\BoardSelect;
use LightPortal\UI\Partials\CategorySelect;
use LightPortal\UI\Partials\ContentClassSelect;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\Utils\MessageIndex;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['no'] = 'No';
    Lang::$txt['lp_block_areas_subtext'] = 'Select areas';
    Lang::$txt['lp_frontpage_topics_select'] = 'Select topics';
    Lang::$txt['lp_frontpage_topics_no_items'] = 'No topics';

    Utils::$context['user'] = ['is_admin' => true];
    Utils::$context['lp_block'] = ['areas' => 'home,forum'];
    Utils::$context['lp_page'] = ['permissions' => 0, 'options' => ['show_in_menu' => true]];

    Config::$modSettings['recycle_board'] = null;

    $messageIndexMock = Mockery::mock('alias:' . MessageIndex::class);
    $messageIndexMock->shouldReceive('getBoardList')->andReturn([['name' => 'Test Category', 'boards' => [1 => ['name' => 'Test Board']]]]);
});

afterEach(function () {
    Mockery::close();
});

it('creates action select', function () {
    $select = SelectFactory::action();

    expect($select)->toBeInstanceOf(ActionSelect::class);
});

it('creates area select', function () {
    $select = SelectFactory::area();

    expect($select)->toBeInstanceOf(AreaSelect::class);
});

it('creates board select', function () {
    $select = SelectFactory::board();

    expect($select)->toBeInstanceOf(BoardSelect::class);
});

it('creates category select', function () {
    $select = SelectFactory::category();

    expect($select)->toBeInstanceOf(CategorySelect::class);
});

it('creates content class select', function () {
    $select = SelectFactory::contentClass();

    expect($select)->toBeInstanceOf(ContentClassSelect::class);
});

it('creates select by type', function () {
    $select = SelectFactory::create('action');

    expect($select)->toBeInstanceOf(ActionSelect::class);
});

it('throws exception for unknown type', function () {
    expect(fn() => SelectFactory::create('unknown'))->toThrow(InvalidArgumentException::class);
});

it('checks that all created selects implement SelectInterface', function () {
    $types = ['action', 'area', 'board', 'content_class', 'entry_type', 'icon', 'page_icon', 'permission', 'placement', 'status', 'title_class', 'topic'];

    foreach ($types as $type) {
        $select = SelectFactory::create($type);
        expect($select)->toBeInstanceOf(SelectInterface::class);
    }
});

<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\PageList;
use LightPortal\Lists\TagList;
use LightPortal\UI\Partials\ActionSelect;
use LightPortal\UI\Partials\AreaSelect;
use LightPortal\UI\Partials\BoardSelect;
use LightPortal\UI\Partials\CategorySelect;
use LightPortal\UI\Partials\ContentClassSelect;
use LightPortal\UI\Partials\IconSelect;
use LightPortal\UI\Partials\PageSelect;
use LightPortal\UI\Partials\PageSlugSelect;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\UI\Partials\SelectInterface;
use LightPortal\UI\Partials\TagSelect;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\MessageIndex;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['no'] = 'No';
    Lang::$txt['lp_block_areas_subtext'] = 'Select areas';
    Lang::$txt['lp_frontpage_topics_select'] = 'Select topics';
    Lang::$txt['lp_frontpage_topics_no_items'] = 'No topics';
    Lang::$txt['lp_no_category'] = 'Uncategorized';

    Utils::$context['user'] = ['is_admin' => true];
    Utils::$context['lp_block'] = ['areas' => 'home,forum'];
    Utils::$context['lp_page'] = ['permissions' => 0, 'options' => ['show_in_menu' => true]];

    Config::$modSettings['recycle_board'] = null;

    $messageIndexMock = mock(MessageIndex::class)->makePartial();
    $messageIndexMock->shouldReceive('getBoardList')->andReturn([['name' => 'Test Category', 'boards' => [1 => ['name' => 'Test Board']]]]);

    // Mock CacheInterface to return array instead of null for CategoryList
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('withKey')->andReturn($cacheMock);
    $cacheMock->shouldReceive('setFallback')->andReturn([]);
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $categoryListMock = mock(CategoryList::class)->makePartial();
    AppMockRegistry::set(CategoryList::class, $categoryListMock);

    $pageListMock = mock(PageList::class)->makePartial();
    AppMockRegistry::set(PageList::class, $pageListMock);

    $tagListMock = mock(TagList::class)->makePartial();
    AppMockRegistry::set(TagList::class, $tagListMock);
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

it('creates icon select', function () {
    $select = SelectFactory::icon();

    expect($select)->toBeInstanceOf(IconSelect::class);
});

it('creates page select', function () {
    $select = SelectFactory::page();

    expect($select)->toBeInstanceOf(PageSelect::class);
});

it('creates page slug select', function () {
    $select = SelectFactory::pageSlug();

    expect($select)->toBeInstanceOf(PageSlugSelect::class);
});

it('creates tag select', function () {
    $select = SelectFactory::tag();

    expect($select)->toBeInstanceOf(TagSelect::class);
});

it('creates select by type', function () {
    $select = SelectFactory::create('action');

    expect($select)->toBeInstanceOf(ActionSelect::class);
});

it('throws exception for unknown type', function () {
    expect(fn() => SelectFactory::create('unknown'))->toThrow(InvalidArgumentException::class);
});

it('checks that all created selects implement SelectInterface', function () {
    $types = ['action', 'area', 'board', 'category', 'content_class', 'entry_type', 'icon', 'page', 'page_icon', 'page_slug', 'permission', 'placement', 'status', 'tag', 'title_class', 'topic'];

    foreach ($types as $type) {
        $select = SelectFactory::create($type);
        expect($select)->toBeInstanceOf(SelectInterface::class);
    }
});

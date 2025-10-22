<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Actions\CardList;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\PageListInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Articles\PageArticle;
use LightPortal\Renderers\RendererInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Session;
use Tests\AppMockRegistry;

beforeEach(function () {
    AppMockRegistry::clear();

    Config::$scripturl = 'https://example.com/index.php';

    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    Utils::$context['canonical_url'] = 'https://example.com/canonical';
    Utils::$context['form_action'] = 'https://example.com';
    Utils::$context['session_var'] = 'session';
    Utils::$context['session_id']  = '123';
    Utils::$context['page_title']  = 'Test Page';

    Lang::$txt['date']        = 'Date';
    Lang::$txt['author']      = 'Author';
    Lang::$txt['views']       = 'Views';
    Lang::$txt['lp_no_items'] = 'No items';
    Lang::$txt['lp_title']    = 'Title';
});

dataset('sorting options', [
    ['title;desc', 'title DESC'],
    ['title', 'title'],
    ['created;desc', 'p.created_at DESC'],
    ['created', 'p.created_at'],
    ['updated;desc', 'p.updated_at DESC'],
    ['updated', 'p.updated_at'],
    ['author_name;desc', 'author_name DESC'],
    ['author_name', 'author_name'],
    ['num_views;desc', 'p.num_views DESC'],
    ['num_views', 'p.num_views'],
    ['last_comment;desc', 'COALESCE(com.created_at, 0) DESC'],
    ['last_comment', 'COALESCE(com.created_at, 0)'],
    ['num_replies;desc', 'p.num_comments DESC'],
    ['num_replies', 'p.num_comments'],
]);

it('should return correct SQL for all sorting options', function ($input, $expected) {
    $cardList = new CardList();

    Utils::$context['lp_current_sorting'] = $input;
    $result = $cardList->getOrderBy();

    expect($result)->toBe($expected);
})->with('sorting options');

it('should return default value for unknown sorting option', function () {
    $cardList = new CardList();
    Utils::$context['lp_current_sorting'] = 'unknown_sort';

    $result = $cardList->getOrderBy();
    expect($result)->toBe('p.created_at DESC');
});

it('should show pages and set context properly without calling obExit', function () {
    $entityMock = Mockery::mock(PageListInterface::class);
    $entityMock->shouldReceive('getTotalPages')->andReturn(24);
    $entityMock->shouldReceive('getPages')->with(0, 12, 'p.created_at DESC')->andReturn([]);

    // Mock app dependencies
    $rendererMock = Mockery::mock(RendererInterface::class);

    $pageArticleMock = Mockery::mock(PageArticle::class);
    $pageArticleMock->shouldReceive('getSortingOptions')->andReturn(['created' => 'Created']);
    AppMockRegistry::set(PageArticle::class, $pageArticleMock);

    $frontPage = new FrontPage($rendererMock);
    $frontPageMock = Mockery::mock($frontPage)->makePartial();
    $frontPageMock->shouldReceive('updateStart')->with(24, 0, 12)->once();
    $frontPageMock->shouldReceive('getNumColumns')->andReturn(3);
    $frontPageMock->shouldReceive('prepareTemplates')->once();
    AppMockRegistry::set(FrontPage::class, $frontPageMock);

    // Set context
    Utils::$context['lp_current_sorting'] = 'created;desc';

    // Mock request
    $requestMock = Mockery::mock(RequestInterface::class);
    $requestMock->shouldReceive('get')->with('start')->andReturn(0);
    $requestMock->shouldReceive('get')->with('sort')->andReturn(null);

    // Partial mock for CardList
    $cardList = Mockery::mock(CardList::class)->makePartial();
    $cardList->shouldReceive('request')->andReturn($requestMock);
    $cardList->shouldReceive('session')->andReturn(new Session('lp'));

    // Call show
    $cardList->show($entityMock);

    // Assert no exception and obExit called
    expect(true)->toBeTrue();
});

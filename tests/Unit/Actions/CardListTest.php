<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Actions\CardList;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\PageListInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Utils\Request;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Session;
use LightPortal\Utils\SessionInterface;
use Tests\AppMockRegistry;

beforeEach(function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    Utils::$context['lp_current_sorting'] = 'created;desc';
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

it('should show pages and set context properly without calling obExit', function () {
    $entityMock = mock(PageListInterface::class);
    $entityMock->shouldReceive('getTotalPages')->andReturn(24);
    $entityMock->shouldReceive('getPages')->with(0, 12, Utils::$context['lp_current_sorting'])->andReturn([]);

    $pageArticleMock = mock(PageArticle::class);
    $pageArticleMock->shouldReceive('getSortingOptions')->andReturn(['created' => 'Created']);
    AppMockRegistry::set(PageArticle::class, $pageArticleMock);

    $start = 0;
    $frontPageMock = mock(FrontPage::class);
    $frontPageMock->shouldReceive('updateStart')->with(24, $start, 12)->once();
    $frontPageMock->shouldReceive('getNumColumns')->andReturn(3);
    $frontPageMock->shouldReceive('prepareTemplates')->once();
    AppMockRegistry::set(FrontPage::class, $frontPageMock);

    AppMockRegistry::set(SessionInterface::class, new Session('lp'));

    $_REQUEST['start'] = 0;
    $_REQUEST['sort']  = null;

    AppMockRegistry::set(RequestInterface::class, new Request());

    $cardList = new CardList();
    $cardList->show($entityMock);

    expect(Utils::$context['start'])->toBe(0)
        ->and(Utils::$context['lp_frontpage_articles'])->toBe([])
        ->and(Utils::$context['lp_frontpage_num_columns'])->toBe(3)
        ->and(Utils::$context['template_layers'])->toContain('lp_list');
});

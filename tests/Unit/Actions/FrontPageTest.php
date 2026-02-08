<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\ActionInterface;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Renderers\RendererInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(FrontPage::class)
    ->toImplement(ActionInterface::class);

afterEach(function () {
    AppMockRegistry::clear();
});

function makeFrontPage(): FrontPage
{
    $renderer = mock(RendererInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $article = mock(ArticleInterface::class);
    AppMockRegistry::set(PageArticle::class, $article);

    return new FrontPage($renderer, $dispatcher);
}

it('calculates number of columns from settings', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    Config::$modSettings['lp_frontpage_num_columns'] = '';
    $frontPage = makeFrontPage();
    expect($frontPage->getNumColumns())->toBe(12);

    Config::$modSettings['lp_frontpage_num_columns'] = '2';
    $frontPage = makeFrontPage();
    expect($frontPage->getNumColumns())->toBe(4);
});

it('selects empty template when no articles', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [],
    ];
    Utils::$context = &$GLOBALS['context'];

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    expect($accessor->callMethod('getCurrentTemplate'))->toBe('empty');
});

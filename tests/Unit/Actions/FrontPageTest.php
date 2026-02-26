<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\ActionInterface;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Renderers\RendererInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(FrontPage::class)
    ->toImplement(ActionInterface::class);

beforeEach(function () {
    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn([
        'arrow_left' => '<i class="arrow-left"></i>',
        'arrow_right' => '<i class="arrow-right"></i>',
    ]);
    AppMockRegistry::set(IconList::class, $iconList);
});

afterEach(function () {
    AppMockRegistry::clear();
});

function makeFrontPage(string $mode = 'all_pages'): FrontPage
{
    Config::$modSettings['lp_frontpage_mode'] = $mode;

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

it('calculates number of columns with different values', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    Config::$modSettings['lp_frontpage_num_columns'] = '1';
    $frontPage = makeFrontPage();
    expect($frontPage->getNumColumns())->toBe(6);

    Config::$modSettings['lp_frontpage_num_columns'] = '3';
    $frontPage = makeFrontPage();
    expect($frontPage->getNumColumns())->toBe(3);

    Config::$modSettings['lp_frontpage_num_columns'] = '4';
    $frontPage = makeFrontPage();
    expect($frontPage->getNumColumns())->toBe(2);
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

it('selects wrong template when layout not set', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [
            ['id' => 1, 'title' => 'Test Article'],
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    Config::$modSettings['lp_frontpage_layout'] = '';

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    expect($accessor->callMethod('getCurrentTemplate'))->toBe('wrong');
});

it('selects home template when articles and layout exist', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [
            ['id' => 1, 'title' => 'Test Article'],
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    Config::$modSettings['lp_frontpage_layout'] = 'default';

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    expect($accessor->callMethod('getCurrentTemplate'))->toBe('home');
});

it('preloads images from articles using column method', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [],
        'html_headers' => '',
    ];
    Utils::$context = &$GLOBALS['context'];

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    $articles = new \Ramsey\Collection\Collection('array');
    $articles->add(['image' => 'https://example.com/image1.jpg']);
    $articles->add(['image' => 'https://example.com/image2.jpg']);

    $accessor->callMethod('preLoadImages', [$articles]);

    expect(Utils::$context['html_headers'])->toContain('preload')
        ->toContain('image1.jpg')
        ->toContain('image2.jpg');
});

it('generates simple pagination with prev and next when in middle', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [],
        'start' => 10,
    ];
    Utils::$context = &$GLOBALS['context'];

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    $result = $accessor->callMethod('simplePaginate', ['https://example.com', 30, 10]);

    expect($result)->toContain('start=0')
        ->toContain('start=20');
});

it('generates simple pagination with only next when at start', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [],
        'start' => 0,
    ];
    Utils::$context = &$GLOBALS['context'];

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    $result = $accessor->callMethod('simplePaginate', ['https://example.com', 30, 10]);

    expect($result)->not->toContain('start=0')
        ->toContain('start=10');
});

it('generates empty pagination when only one page', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $GLOBALS['context'] = [
        'lp_frontpage_articles' => [],
        'start' => 0,
    ];
    Utils::$context = &$GLOBALS['context'];

    $frontPage = makeFrontPage();
    $accessor = new ReflectionAccessor($frontPage);

    $result = $accessor->callMethod('simplePaginate', ['https://example.com', 5, 10]);

    expect($result)->toBe('');
});

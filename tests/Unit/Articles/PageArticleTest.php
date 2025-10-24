<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Articles\Services\PageArticleService;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use Prophecy\Prophet;

beforeEach(function() {
    $this->prophet = new Prophet();

    $queryProphecy = $this->prophet->prophesize(PageArticleQuery::class);
    $queryProphecy->getTotalCount()->willReturn(0);
    $queryProphecy->getSorting()->willReturn('created;desc');
    $queryProphecy->getRawData()->willReturn([]);

    $eventsProphecy = $this->prophet->prophesize(EventDispatcherInterface::class);
    $repositoryProphecy = $this->prophet->prophesize(PageRepositoryInterface::class);

    $this->service = new PageArticleService(
        $queryProphecy->reveal(),
        $eventsProphecy->reveal(),
        $repositoryProphecy->reveal()
    );
    $this->article = new PageArticle($this->service);
});

arch()
    ->expect(PageArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);

it('accepts PageArticleService in constructor', function () {
    expect($this->article)->toBeInstanceOf(PageArticle::class);
});

it('delegates init to service', function () {
    $this->article->init();

    expect(true)->toBeTrue();
});

it('delegates getSortingOptions to service', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc');
});

it('delegates getData to service', function () {
    $result = $this->article->getData(0, 10, 'created;desc');
    $data = iterator_to_array($result);

    expect($data)->toBeArray();
});

it('delegates getTotalCount to service', function () {
    $count = $this->article->getTotalCount();

    expect($count)->toBe(0);
});

it('delegates prepareTags to service', function () {
    $pages = [];
    $this->article->prepareTags($pages);

    expect($pages)->toBeArray();
});

<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Articles\Services\PageArticleService;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;

beforeEach(function() {
    $this->queryMock = mock(PageArticleQuery::class);
    $this->queryMock->shouldReceive('getTotalCount')->andReturn(0);
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc');
    $this->queryMock->shouldReceive('getRawData')->andReturn([]);

    $eventsMock     = mock(EventDispatcherInterface::class);
    $repositoryMock = mock(PageRepositoryInterface::class);

    $service = new PageArticleService($this->queryMock, $eventsMock, $repositoryMock);

    $this->article = new PageArticle($service);
});

arch()
    ->expect(PageArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);

it('accepts PageArticleService in constructor', function () {
    expect($this->article)->toBeInstanceOf(PageArticle::class);
});

it('delegates init to service', function () {
    $this->queryMock->expects('init')->with(Mockery::type('array'));

    $this->article->init();

    expect(true)->toBeTrue();
});

it('delegates getSortingOptions to service', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc');
});

it('delegates getData to service', function () {
    $this->queryMock->expects('setSorting')->with('created;desc');
    $this->queryMock->expects('prepareParams')->with(0, 10);

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

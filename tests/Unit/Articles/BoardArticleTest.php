<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\BoardArticle;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Articles\Services\BoardArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;

beforeEach(function() {
    $this->prophet = new Prophet();

    $queryProphecy = $this->prophet->prophesize(BoardArticleQuery::class);
    $queryProphecy->getTotalCount()->willReturn(0);
    $queryProphecy->getSorting()->willReturn('created;desc');
    $queryProphecy->getRawData()->willReturn([]);

    $eventsProphecy = $this->prophet->prophesize(EventDispatcherInterface::class);

    $this->service = new BoardArticleService($queryProphecy->reveal(), $eventsProphecy->reveal());
    $this->article = new BoardArticle($this->service);
});

arch()
    ->expect(BoardArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);

it('accepts BoardArticleService in constructor', function () {
    expect($this->article)->toBeInstanceOf(BoardArticle::class);
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

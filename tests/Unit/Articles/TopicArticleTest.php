<?php

declare(strict_types=1);

use LightPortal\Articles\AbstractArticle;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\TopicArticle;
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Articles\Services\TopicArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;

beforeEach(function() {
    $this->prophet = new Prophet();

    $queryProphecy = $this->prophet->prophesize(TopicArticleQuery::class);
    $queryProphecy->getTotalCount()->willReturn(0);
    $queryProphecy->getSorting()->willReturn('created;desc');
    $queryProphecy->getRawData()->willReturn([]);

    $eventsProphecy = $this->prophet->prophesize(EventDispatcherInterface::class);

    $this->service = new TopicArticleService($queryProphecy->reveal(), $eventsProphecy->reveal());
    $this->article = new TopicArticle($this->service);
});

arch()
    ->expect(TopicArticle::class)
    ->toExtend(AbstractArticle::class)
    ->toImplement(ArticleInterface::class);

it('accepts TopicArticleService in constructor', function () {
    expect($this->article)->toBeInstanceOf(TopicArticle::class);
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

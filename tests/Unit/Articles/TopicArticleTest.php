<?php

declare(strict_types=1);

use LightPortal\Articles\TopicArticle;
use LightPortal\Articles\Services\TopicArticleService;
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Events\EventDispatcherInterface;

beforeEach(function() {
    $this->queryMock = mock(TopicArticleQuery::class);
    $this->events    = mock(EventDispatcherInterface::class);
    $this->service   = new TopicArticleService($this->queryMock, $this->events);
    $this->article   = new TopicArticle($this->service);
});

it('initializes service on init', function () {
    $this->queryMock->expects('init')
        ->with(Mockery::on(function ($params) {
            return isset($params['current_member']) && isset($params['is_approved']);
        }))->once();

    $this->article->init();

    expect(true)->toBeTrue();
});

it('returns sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('updated;desc')
        ->and($options)->toHaveKey('author_name;desc');
});

it('returns data from service', function () {
    $this->queryMock->expects('setSorting')->with('created;desc')->once();
    $this->queryMock->expects('prepareParams')->with(0, 10)->once();
    $this->queryMock->expects('getRawData')->andReturn([]);

    $data = iterator_to_array($this->article->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray();
});

it('returns total count from service', function () {
    $this->queryMock->expects()->getTotalCount()->andReturn(30);

    $count = $this->article->getTotalCount();

    expect($count)->toBe(30);
});

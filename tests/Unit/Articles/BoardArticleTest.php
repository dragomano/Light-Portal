<?php

declare(strict_types=1);

use LightPortal\Articles\BoardArticle;
use LightPortal\Articles\Services\BoardArticleService;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Events\EventDispatcherInterface;

beforeEach(function() {
    $this->queryMock = mock(BoardArticleQuery::class);
    $this->events    = mock(EventDispatcherInterface::class);
    $this->service   = new BoardArticleService($this->queryMock, $this->events);
    $this->article   = new BoardArticle($this->service);
});

it('initializes service on init', function () {
    $this->queryMock->expects('init')->with(Mockery::type('array'));

    $this->article->init();

    expect(true)->toBeTrue();
});

it('returns sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('updated;desc');
});

it('returns data from service', function () {
    $this->queryMock->expects('setSorting')->with('created;desc');
    $this->queryMock->expects('prepareParams')->with(0, 10);
    $this->queryMock->expects('getRawData')->andReturn([]);

    $data = iterator_to_array($this->article->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray();
});

it('returns total count from service', function () {
    $this->queryMock->expects('getTotalCount')->andReturn(25);

    $count = $this->article->getTotalCount();

    expect($count)->toBe(25);
});

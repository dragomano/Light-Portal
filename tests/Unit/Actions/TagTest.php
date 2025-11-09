<?php

declare(strict_types=1);

use LightPortal\Actions\IndexInterface;
use LightPortal\Actions\PageListInterface;
use LightPortal\Actions\Tag;
use LightPortal\Actions\ActionInterface;
use LightPortal\Utils\RequestInterface;

arch()
    ->expect(Tag::class)
    ->toImplement(ActionInterface::class);

it('calls tagIndex->show() when id is not in request', function () {
    $pageListMock = mock(PageListInterface::class);
    $pageListMock->expects('show')->never();

    $tagIndexMock = mock(IndexInterface::class);
    $tagIndexMock->expects('show')->once();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('id')->andReturn(true);

    $tagMock = mock(Tag::class, [$pageListMock, $tagIndexMock])
        ->makePartial()
        ->shouldReceive('request')
        ->andReturn($requestMock)
        ->getMock();

    $tagMock->show();
});

it('calls pageList->show() when id is in request', function () {
    $pageListMock = mock(PageListInterface::class);
    $pageListMock->expects('show')->once();

    $tagIndexMock = mock(IndexInterface::class);
    $tagIndexMock->expects('show')->never();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('id')->andReturn(false);

    $tagMock = mock(Tag::class, [$pageListMock, $tagIndexMock])
        ->makePartial()
        ->shouldReceive('request')
        ->andReturn($requestMock)
        ->getMock();

    $tagMock->show();
});

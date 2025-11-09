<?php

declare(strict_types=1);

use LightPortal\Actions\Category;
use LightPortal\Actions\ActionInterface;
use LightPortal\Actions\IndexInterface;
use LightPortal\Actions\PageListInterface;
use LightPortal\Utils\RequestInterface;

arch()
    ->expect(Category::class)
    ->toImplement(ActionInterface::class);

it('calls categoryIndex->show() when id is not in request', function () {
    $pageListMock = mock(PageListInterface::class);
    $pageListMock->expects('show')->never();

    $categoryIndexMock = mock(IndexInterface::class);
    $categoryIndexMock->expects('show')->once();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('id')->andReturn(true);

    $categoryMock = mock(Category::class, [$pageListMock, $categoryIndexMock])
        ->makePartial()
        ->shouldReceive('request')
        ->andReturn($requestMock)
        ->getMock();

    $categoryMock->show();
});

it('calls pageList->show() when id is in request', function () {
    $pageListMock = mock(PageListInterface::class);
    $pageListMock->expects('show')->once();

    $categoryIndexMock = mock(IndexInterface::class);
    $categoryIndexMock->expects('show')->never();

    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with('id')->andReturn(false);

    $categoryMock = mock(Category::class, [$pageListMock, $categoryIndexMock])
        ->makePartial()
        ->shouldReceive('request')
        ->andReturn($requestMock)
        ->getMock();

    $categoryMock->show();
});

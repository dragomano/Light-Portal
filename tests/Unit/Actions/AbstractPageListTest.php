<?php

declare(strict_types=1);

use LightPortal\Actions\AbstractPageList;
use LightPortal\Actions\CardListInterface;
use LightPortal\Actions\PageListInterface;
use LightPortal\Articles\Services\ArticleServiceInterface;

class TestPageListAction extends AbstractPageList
{
    public function show(): void {}
}

arch()
    ->expect(TestPageListAction::class)
    ->toImplement(PageListInterface::class);

it('initializes article service and exposes page data', function () {
    $cardList = mock(CardListInterface::class);

    $articleService = mock(ArticleServiceInterface::class);
    $articleService->shouldReceive('init')->once();
    $articleService->shouldReceive('getData')
        ->with(0, 10, 'created')
        ->andReturn(new ArrayIterator([
            ['id' => 1],
            ['id' => 2],
        ]));
    $articleService->shouldReceive('getTotalCount')->andReturn(12);

    $action = new TestPageListAction($cardList, $articleService);

    $pages = $action->getPages(0, 10, 'created');

    expect($pages)->toHaveCount(2)
        ->and($pages[0]['id'])->toBe(1)
        ->and($action->getTotalPages())->toBe(12);
});

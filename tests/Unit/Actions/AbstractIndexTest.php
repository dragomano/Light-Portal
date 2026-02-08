<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use LightPortal\Actions\AbstractIndex;
use LightPortal\Actions\IndexInterface;
use LightPortal\Repositories\RepositoryInterface;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

class TestIndexAction extends AbstractIndex
{
    public function show(): void {}

    public function getAll(int $start, int $limit, string $sort): array
    {
        return $this->repository->getAll($start, $limit, $sort);
    }

    public function getTotalCount(): int
    {
        return $this->repository->getTotalCount();
    }
}

arch()
    ->expect(TestIndexAction::class)
    ->toImplement(IndexInterface::class);

afterEach(function () {
    AppMockRegistry::clear();
});

it('stores repository and resolves helpers', function () {
    $repository = mock(RepositoryInterface::class);
    $repository->shouldReceive('getAll')->andReturn([]);
    $repository->shouldReceive('getTotalCount')->andReturn(0);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $presenter = mock(TablePresenterInterface::class);

    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);
    AppMockRegistry::set(TablePresenterInterface::class, $presenter);

    $action = new TestIndexAction($repository);
    $accessor = new ReflectionAccessor($action);

    expect($accessor->getProperty('repository'))->toBe($repository)
        ->and($action->breadcrumbs())->toBe($breadcrumbs)
        ->and($action->getTablePresenter())->toBe($presenter);
});

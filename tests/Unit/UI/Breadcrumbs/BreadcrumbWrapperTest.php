<?php

declare(strict_types=1);

use Bugo\Bricks\Breadcrumbs\BreadcrumbBuilder;
use Bugo\Bricks\Breadcrumbs\BreadcrumbItem;
use Bugo\Bricks\Breadcrumbs\Interfaces\BreadcrumbPresenterInterface;
use Bugo\Compat\Utils;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;

beforeEach(function () {
    Utils::$context['linktree'] = [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Blog', 'url' => '/blog', 'extra_before' => '<i>', 'extra_after' => '</i>'],
    ];
});

describe('BreadcrumbWrapper', function () {
    it('hydrates builder from existing linktree', function () {
        $builder = BreadcrumbBuilder::make();
        $presenter = mock(BreadcrumbPresenterInterface::class);
        $presenter->shouldReceive('show')->never();

        $wrapper = new BreadcrumbWrapper($builder, $presenter);
        $items = $wrapper->getAll();

        expect($items)->toHaveCount(2)
            ->and($items[0]->getName())->toBe('Home')
            ->and($items[0]->getUrl())->toBe('/')
            ->and($items[1]->getBefore())->toBe('<i>')
            ->and($items[1]->getAfter())->toBe('</i>');
    });

    it('returns self for mutators and calls presenter', function () {
        $builder = BreadcrumbBuilder::make();
        $presenter = mock(BreadcrumbPresenterInterface::class);
        $presenter->shouldReceive('show')->twice();

        $wrapper = new BreadcrumbWrapper($builder, $presenter);
        $result = $wrapper->add('About', '/about');
        $wrapper->remove(0);

        expect($result)->toBeInstanceOf(BreadcrumbWrapper::class);
    });

    it('returns raw value for non-mutator methods', function () {
        $builder = BreadcrumbBuilder::make();
        $presenter = mock(BreadcrumbPresenterInterface::class);
        $presenter->shouldReceive('show')->never();

        $wrapper = new BreadcrumbWrapper($builder, $presenter);
        $items = $wrapper->getAll();

        expect($items)->toBeArray();
    });

    it('supports addItem and update mutators', function () {
        $builder = BreadcrumbBuilder::make();
        $presenter = mock(BreadcrumbPresenterInterface::class);
        $presenter->shouldReceive('show')->times(2);

        $wrapper = new BreadcrumbWrapper($builder, $presenter);
        $wrapper->addItem(BreadcrumbItem::make('Docs', '/docs'));
        $wrapper->update(0, 'name', 'Docs Updated');

        $items = $wrapper->getAll();

        expect($items[0]->getName())->toBe('Docs Updated');
    });
});

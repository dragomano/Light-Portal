<?php

declare(strict_types=1);

use Bugo\Compat\Routable;
use LightPortal\Enums\Action;
use LightPortal\Routes\Page;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;

arch()
    ->expect(Page::class)
    ->toImplement(Routable::class);

describe('Page route', function () {
    afterEach(function () {
        AppMockRegistry::clear();
    });

    it('builds route for page slug', function () {
        $params = [
            LP_PAGE_PARAM => 'my-page',
            'foo' => 'bar',
        ];

        $result = Page::buildRoute($params);

        expect($result['route'])->toBe([Action::PAGES->value, 'my-page'])
            ->and($result['params'])->toBe(['foo' => 'bar']);
    });

    it('builds empty route when no page param', function () {
        $params = ['foo' => 'bar'];
        $result = Page::buildRoute($params);

        expect($result['route'])->toBe([])
            ->and($result['params'])->toBe($params);
    });

    it('redirects when route has no slug', function () {
        $response = mock(ResponseInterface::class);
        $response->shouldReceive('redirect')->once()->andReturnNull();
        AppMockRegistry::set(ResponseInterface::class, $response);

        $result = Page::parseRoute([Action::PAGES->value]);

        expect($result)->toHaveKey(LP_PAGE_PARAM)
            ->and($result[LP_PAGE_PARAM])->toBeNull();
    });

    it('parses page slug from route', function () {
        $result = Page::parseRoute([Action::PAGES->value, 'my-page']);

        expect($result)->toBe([
            LP_PAGE_PARAM => 'my-page',
        ]);
    });
});

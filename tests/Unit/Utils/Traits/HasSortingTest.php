<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\SessionInterface;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasSorting;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

beforeEach(function () {
    Utils::$context['lp_sorting_options'] = [];
    Utils::$context['lp_current_sorting'] = null;

    $this->requestMock = mock(RequestInterface::class);
    $this->sessionMock = mock(SessionInterface::class);
    $this->sessionMock->shouldReceive('withKey')->andReturnSelf();
    $this->sessionMock->shouldReceive('put')->andReturn(null);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
    AppMockRegistry::set(SessionInterface::class, $this->sessionMock);

    $this->testClass = new class {
        use HasRequest;
        use HasSorting;
    };

    $this->reflection = new ReflectionAccessor($this->testClass);
});

afterEach(function () {
    AppMockRegistry::clear(RequestInterface::class);
    AppMockRegistry::clear(SessionInterface::class);
});

describe('HasSorting::prepareSorting()', function () {
    it('uses sort from request when provided', function () {
        $sessionKey = 'lp_article_sorting';
        $sortValue = 'title;asc';

        $this->requestMock->shouldReceive('get')->with('sort')->andReturn($sortValue);

        $this->testClass->prepareSorting($sessionKey);

        expect(Utils::$context['lp_current_sorting'])->toBe($sortValue);
    });

    it('uses sort from session when request sort is null and session has value', function () {
        $sessionKey = 'lp_article_sorting';
        $sessionSortValue = 'created;desc';
        $defaultSortValue = 'created;desc';

        $this->requestMock->shouldReceive('get')->with('sort')->andReturn(null);
        $this->sessionMock->shouldReceive('isEmpty')->with($sessionKey)->andReturn(false);
        $this->sessionMock->shouldReceive('get')->with($sessionKey)->andReturn($sessionSortValue);
        Config::$modSettings['lp_frontpage_article_sorting'] = $defaultSortValue;

        $this->testClass->prepareSorting($sessionKey);

        expect(Utils::$context['lp_current_sorting'])->toBe($sessionSortValue);
    });

    it('uses default sort when request sort is null and session is empty', function () {
        $sessionKey = 'lp_article_sorting';
        $defaultSortValue = 'created;desc';

        $this->requestMock->shouldReceive('get')->with('sort')->andReturn(null);
        $this->sessionMock->shouldReceive('isEmpty')->with($sessionKey)->andReturn(true);
        Config::$modSettings['lp_frontpage_article_sorting'] = $defaultSortValue;

        $this->testClass->prepareSorting($sessionKey);

        expect(Utils::$context['lp_current_sorting'])->toBe($defaultSortValue);
    });
});

describe('HasSorting::prepareSortingOptions()', function () {
    it('prepares sorting options from article', function () {
        $articleMock = mock(ArticleInterface::class);
        $sortingOptions = [
            'created;desc' => 'Newest first',
            'created;asc' => 'Oldest first',
            'title;asc' => 'By title A-Z',
            'title;desc' => 'By title Z-A',
        ];

        $articleMock->shouldReceive('getSortingOptions')->andReturn($sortingOptions);

        $this->testClass->prepareSortingOptions($articleMock);

        expect(Utils::$context['lp_sorting_options'])->toBe($sortingOptions);
    });
});

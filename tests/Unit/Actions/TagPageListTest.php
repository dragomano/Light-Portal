<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Actions\CardListInterface;
use LightPortal\Actions\TagPageList;
use LightPortal\Articles\Services\TagPageArticleService;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Lists\TagList;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;

afterEach(function () {
    AppMockRegistry::clear();
});

it('shows pages for selected tag', function () {
    Lang::$txt['lp_all_page_tags'] = 'All tags';
    Lang::$txt['lp_all_tags_by_key'] = 'Pages tagged with %s';
    Lang::$txt['back'] = 'Back';

    Config::$scripturl = 'https://example.com/index.php';
    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $request = mock(RequestInterface::class);
    $request->shouldReceive('get')->with('id')->andReturn('5');
    AppMockRegistry::set(RequestInterface::class, $request);

    $tagList = new class {
        public function __invoke(): array
        {
            return [
                5 => [
                    'id' => 5,
                    'title' => 'News',
                    'description' => 'Latest news',
                    'icon' => '<i class="fas fa-newspaper"></i>',
                ],
            ];
        }
    };
    AppMockRegistry::set(TagList::class, $tagList);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->twice()->andReturnSelf();
    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);

    $cardList = mock(CardListInterface::class);
    $cardList->shouldReceive('show')->once();

    $articleService = mock(TagPageArticleService::class);
    $articleService->shouldReceive('init')->once();

    $action = new TagPageList($cardList, $articleService);
    $action->show();

    expect(Utils::$context['page_title'])->toBe('Pages tagged with News')
        ->and(Utils::$context['canonical_url'])->toBe(PortalSubAction::TAGS->url() . ';id=5')
        ->and(Utils::$context['robot_no_index'])->toBeTrue();
});
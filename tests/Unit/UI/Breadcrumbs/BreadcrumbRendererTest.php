<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\Breadcrumbs\BreadcrumbRenderer;

beforeEach(function () {
    Utils::$context['linktree'] = ['existing'];
});

describe('BreadcrumbRenderer', function () {
    it('rebuilds linktree from data', function () {
        $renderer = new BreadcrumbRenderer();

        $renderer->render([
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Pages', 'before' => '<b>', 'after' => '</b>'],
        ]);

        expect(Utils::$context['linktree'])->toBe([
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Pages', 'extra_before' => '<b>', 'extra_after' => '</b>'],
        ]);
    });
});

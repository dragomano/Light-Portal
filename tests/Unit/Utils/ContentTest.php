<?php

declare(strict_types=1);

use LightPortal\Utils\Content;
use LightPortal\Enums\ContentType;
use LightPortal\Renderers\PurePHP;
use Bugo\Compat\Utils;
use Tests\AppMockRegistry;

arch()
    ->expect(Content::class)
    ->toHaveMethods(['prepare', 'parse']);

describe('Content::parse', function () {
    afterEach(function () {
        AppMockRegistry::clear();
    });

    it('decodes HTML content', function () {
        $encoded = htmlspecialchars('<strong>Test</strong>', ENT_QUOTES);
        $result = Content::parse($encoded, ContentType::HTML->name());

        expect($result)->toBe(Utils::htmlspecialcharsDecode($encoded));
    });

    it('renders PHP content using PurePHP renderer', function () {
        $renderer = mock(PurePHP::class);
        $renderer->shouldReceive('renderString')
            ->once()
            ->with('echo "ok";')
            ->andReturn('<div>ok</div>');

        AppMockRegistry::set('PurePHP', $renderer);

        $result = Content::parse('echo "ok";', ContentType::PHP->name());

        expect($result)->toBe('<div>ok</div>');
    });
});

<?php

declare(strict_types=1);

use Bugo\Compat\Routable;
use LightPortal\Routes\Forum;

arch()
    ->expect(Forum::class)
    ->toImplement(Routable::class);

describe('Forum route', function () {
    it('builds route and preserves params', function () {
        $params = [
            'action' => 'forum',
            'sa' => 'recent',
            'start' => 20,
        ];

        $result = Forum::buildRoute($params);

        expect($result['route'])->toBe(['forum'])
            ->and($result['params'])->toBe([
                'sa' => 'recent',
                'start' => 20,
            ]);
    });

    it('parses route into params', function () {
        $params = Forum::parseRoute(['forum'], ['foo' => 'bar']);

        expect($params)->toBe([
            'foo' => 'bar',
            'action' => 'forum',
        ]);
    });
});

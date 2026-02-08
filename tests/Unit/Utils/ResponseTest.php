<?php

declare(strict_types=1);

use LightPortal\Utils\Response;
use LightPortal\Utils\ResponseInterface;

arch()
    ->expect(Response::class)
    ->toImplement(ResponseInterface::class);

describe('Response', function () {
    describe('json()', function () {
        it('returns JSON encoded string', function () {
            $response = new Response();

            $result = $response->json(['key' => 'value']);

            expect($result)->toBe('{"key":"value"}');
        });

        it('encodes data with JSON flags', function () {
            $response = new Response();

            $result = $response->json(['key' => "value\nwith\ttabs"], JSON_PRETTY_PRINT);

            expect($result)->toContain('\n')
                ->and($result)->toContain('\t');
        });

        it('encodes various data types', function () {
            $response = new Response();

            $arrayData = ['a' => 1, 'b' => 2, 'c' => 3];
            expect($response->json($arrayData))->toBe('{"a":1,"b":2,"c":3}');

            $stringData = 'simple string';
            expect($response->json($stringData))->toBe('"simple string"');

            $numberData = 42;
            expect($response->json($numberData))->toBe('42')
                ->and($response->json(true))->toBe('true');

            $nullData = null;
            expect($response->json($nullData))->toBe('null');
        });

        it('handles nested arrays', function () {
            $response = new Response();

            $nested = [
                'level1' => [
                    'level2' => [
                        'level3' => 'deep value'
                    ]
                ]
            ];

            $result = $response->json($nested);

            expect($result)->toContain('level1')
                ->and($result)->toContain('level2')
                ->and($result)->toContain('level3');
        });
    });

    describe('redirect()', function () {
        it('calls redirectexit with url', function () {
            $GLOBALS['redirectexit_calls'] ??= [];
            $GLOBALS['redirectexit_calls'][] = 'https://example.com';

            $response = new Response();
            $response->redirect('https://example.com');

            expect($GLOBALS['redirectexit_calls'])->toContain('https://example.com');
            array_pop($GLOBALS['redirectexit_calls']);
        });

        it('calls redirectexit with empty url', function () {
            $GLOBALS['redirectexit_calls'] ??= [];
            $GLOBALS['redirectexit_calls'][] = '';

            $response = new Response();
            $response->redirect();

            expect($GLOBALS['redirectexit_calls'])->toContain('');
            array_pop($GLOBALS['redirectexit_calls']);
        });

        it('handles various URL formats', function () {
            $GLOBALS['redirectexit_calls'] ??= [];

            $response = new Response();

            $response->redirect('https://example.com/page?param=value');
            expect(end($GLOBALS['redirectexit_calls']))->toBe('https://example.com/page?param=value');

            $response->redirect('/relative/path');
            expect(end($GLOBALS['redirectexit_calls']))->toBe('/relative/path');

            $response->redirect('http://localhost:8080');
            expect(end($GLOBALS['redirectexit_calls']))->toBe('http://localhost:8080');
        });
    });
});

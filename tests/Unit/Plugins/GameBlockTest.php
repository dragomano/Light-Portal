<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Plugins\Event;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;
use Tests\TestExitException;

beforeEach(function () {
    foreach (['test_game_block', 'test_scoring_game_block'] as $snake) {
        Lang::$txt['lp_' . $snake] = Lang::$txt['lp_' . $snake] ?? [];
        Utils::$context['lp_' . $snake . '_plugin'] = [];
    }
});

function lpCreateGameRequest(array $params): object
{
    $request = mock(RequestInterface::class);
    $request->shouldReceive('has')->with('api')->andReturn(isset($params['api']));
    $request->shouldReceive('get')->with('api')->andReturn($params['api'] ?? null);
    $request->shouldReceive('get')->with('id')->andReturn($params['id'] ?? null);
    $request->shouldReceive('url')->andReturn($params['url'] ?? 'https://example.com/index.php');

    AppMockRegistry::set(RequestInterface::class, $request);

    return $request;
}

it('detects an api request for this block', function () {
    lpCreateGameRequest(['api' => 'test_game_block', 'id' => '5']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 5]);

    $accessor = new ReflectionAccessor($block);

    expect($accessor->callMethod('isApiRequestForThisBlock', [$event]))->toBeTrue();
});

it('rejects requests without the api parameter', function () {
    lpCreateGameRequest(['id' => '5']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 5]);

    expect((new ReflectionAccessor($block))->callMethod('isApiRequestForThisBlock', [$event]))->toBeFalse();
});

it('rejects requests for another block', function () {
    lpCreateGameRequest(['api' => 'another_block', 'id' => '5']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 5]);

    expect((new ReflectionAccessor($block))->callMethod('isApiRequestForThisBlock', [$event]))->toBeFalse();
});

it('rejects requests with a wrong id', function () {
    lpCreateGameRequest(['api' => 'test_game_block', 'id' => '6']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 5]);

    expect((new ReflectionAccessor($block))->callMethod('isApiRequestForThisBlock', [$event]))->toBeFalse();
});

it('handles an api request by exiting with api data', function () {
    lpCreateGameRequest(['api' => 'test_scoring_game_block', 'id' => '5']);

    $response = mock(ResponseInterface::class);
    $response->shouldReceive('exit')
        ->once()
        ->with(['score' => 42])
        ->andThrow(new TestExitException());

    AppMockRegistry::set(ResponseInterface::class, $response);

    $block = new TestScoringGameBlock();
    $event = new Event((object) ['id' => 5]);

    expect(fn() => (new ReflectionAccessor($block))->callMethod('handleApiRequest', [$event]))
        ->toThrow(TestExitException::class);
});

it('does not handle foreign api requests', function () {
    $response = mock(ResponseInterface::class);
    $response->shouldReceive('exit')->never();

    AppMockRegistry::set(ResponseInterface::class, $response);

    lpCreateGameRequest(['id' => '5']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 5]);

    (new ReflectionAccessor($block))->callMethod('handleApiRequest', [$event]);

    expect(true)->toBeTrue();
});

it('builds an api url with a proper separator', function () {
    lpCreateGameRequest(['url' => 'https://example.com/index.php?action=portal']);

    $block = new TestGameBlock();
    $event = new Event((object) ['id' => 7]);

    expect((new ReflectionAccessor($block))->callMethod('buildApiUrl', [$event]))
        ->toBe('https://example.com/index.php?action=portal;api=test_game_block;id=7');

    lpCreateGameRequest(['url' => 'https://example.com/']);

    expect((new ReflectionAccessor($block))->callMethod('buildApiUrl', [$event]))
        ->toBe('https://example.com/?api=test_game_block;id=7');
});

class TestGameBlock extends \LightPortal\Plugins\GameBlock
{
}

class TestScoringGameBlock extends TestGameBlock
{
    protected function getApiData(Event $e): array
    {
        return ['score' => 42];
    }
}

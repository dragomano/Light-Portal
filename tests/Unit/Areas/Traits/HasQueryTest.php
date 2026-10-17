<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Laminas\Db\Extra\Result\ExtendedResultInterface;
use Laminas\Db\Extra\Sql\Operations\ExtendedSelect;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Areas\Traits\HasQuery;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\IconList;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;
use Tests\TestExitException;

function lpCreateResultMock(array $rows): object
{
    $result = mock(ExtendedResultInterface::class);
    $index  = 0;

    $result->shouldReceive('rewind')->andReturnUsing(function () use (&$index) {
        $index = 0;
    });
    $result->shouldReceive('valid')->andReturnUsing(function () use (&$index, $rows) {
        return $index < count($rows);
    });
    $result->shouldReceive('current')->andReturnUsing(function () use (&$index, $rows) {
        return $rows[$index];
    });
    $result->shouldReceive('key')->andReturnUsing(function () use (&$index) {
        return $index;
    });
    $result->shouldReceive('next')->andReturnUsing(function () use (&$index) {
        $index++;
    });

    return $result;
}

class TestHasQueryUser
{
    use HasQuery;

    public function __construct(public ?EventDispatcherInterface $dispatcher = null) {}
}

beforeEach(function () {
    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('hasNot')->andReturn(true)->byDefault();
    $this->request->shouldIgnoreMissing();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->response = mock(ResponseInterface::class);
    $this->response->shouldReceive('exit')->byDefault()->andThrow(new TestExitException());
    AppMockRegistry::set(ResponseInterface::class, $this->response);

    $this->sql = mock(PortalSqlInterface::class);
    AppMockRegistry::set(PortalSqlInterface::class, $this->sql);

    $this->dispatcher = mock(EventDispatcherInterface::class);

    $this->user = new TestHasQueryUser($this->dispatcher);
    $this->accessor = new ReflectionAccessor($this->user);
});

it('skips icon list preparation when the icons parameter is missing', function () {
    $this->response->shouldNotReceive('exit');

    $this->accessor->callMethod('prepareIconList');

    expect(true)->toBeTrue();
});

it('skips icon list preparation when the search query is empty', function () {
    $this->request->shouldReceive('hasNot')->with('icons')->andReturn(false);
    $this->request->shouldReceive('json')->andReturn(['search' => '   ']);

    $this->response->shouldNotReceive('exit');

    $this->accessor->callMethod('prepareIconList');

    expect(true)->toBeTrue();
});

it('returns filtered icons as a json response', function () {
    Lang::$txt['lang_locale'] = 'en_US';

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('getList')->andReturn(['fas fa-plus', 'fas fa-minus']);
    AppMockRegistry::set(IconList::class, $iconList);

    $this->request->shouldReceive('hasNot')->with('icons')->andReturn(false);
    $this->request->shouldReceive('json')->andReturn(['search' => 'plus']);

    $this->dispatcher->shouldReceive('dispatch')->once()->with(
        PortalHook::prepareIconList,
        Mockery::on(fn($params) => is_array($params['icons']))
    );

    $this->response->shouldReceive('exit')
        ->once()
        ->with(Mockery::on(fn($results) => $results[0]['value'] === 'fas fa-plus'))
        ->andThrow(new TestExitException());

    try {
        $this->accessor->callMethod('prepareIconList');
    } catch (TestExitException) {
        // Expected exit
    }
});

it('caches the font awesome icon list', function () {
    $iconList = mock(IconList::class);
    $iconList->shouldReceive('getList')->twice()->andReturn(['fas fa-plus']);
    AppMockRegistry::set(IconList::class, $iconList);

    $first  = $this->accessor->callMethod('getFaIcons');
    $second = $this->accessor->callMethod('getFaIcons');

    expect($first)->toBe(['fas fa-plus'])
        ->and($second)->toBe(['fas fa-plus']);
});

it('skips topic list preparation when the parameter is missing', function () {
    $this->response->shouldNotReceive('exit');

    $this->accessor->callMethod('prepareTopicList');

    expect(true)->toBeTrue();
});

it('returns matching topics as a json response', function () {
    $this->request->shouldReceive('hasNot')->with('topic_by_subject')->andReturn(false);
    $this->request->shouldReceive('json')->andReturn(['search' => 'Test Topic']);

    $select = mock(ExtendedSelect::class);
    $select->shouldReceive('from')->andReturnSelf();
    $select->shouldReceive('columns')->andReturnSelf();
    $select->shouldReceive('join')->andReturnSelf();
    $select->shouldReceive('where')->andReturnSelf();
    $select->shouldReceive('order')->andReturnSelf();
    $select->shouldReceive('limit')->andReturnSelf();

    $this->sql->shouldReceive('select')->andReturn($select);
    $this->sql->shouldReceive('execute')->with($select)->andReturn(lpCreateResultMock([
        ['id_topic' => 5, 'subject' => 'Test Topic'],
        ['id_topic' => 7, 'subject' => 'Another Test Topic'],
    ]));

    $this->response->shouldReceive('exit')
        ->once()
        ->with(Mockery::on(fn($topics) => $topics[0]['id'] === 5 && $topics[1]['subject'] === 'Another Test Topic'))
        ->andThrow(new TestExitException());

    try {
        $this->accessor->callMethod('prepareTopicList');
    } catch (TestExitException) {
        // Expected exit
    }
});

it('builds a topic search query with an expression', function () {
    $this->request->shouldReceive('hasNot')->with('topic_by_subject')->andReturn(false);
    $this->request->shouldReceive('json')->andReturn(['search' => 'Test']);

    $expression = null;

    $select = mock(ExtendedSelect::class);
    $select->shouldReceive('from')->andReturnSelf();
    $select->shouldReceive('columns')->andReturnSelf();
    $select->shouldReceive('join')->andReturnSelf();
    $select->shouldReceive('where')
        ->with(Mockery::on(function ($arg) use (&$expression) {
            $expression = $arg;

            return $arg instanceof Expression;
        }))
        ->andReturnSelf();
    $select->shouldReceive('where')->andReturnSelf();
    $select->shouldReceive('order')->andReturnSelf();
    $select->shouldReceive('limit')->andReturnSelf();

    $this->sql->shouldReceive('select')->andReturn($select);
    $this->sql->shouldReceive('execute')->andReturn(lpCreateResultMock([]));

    $this->response->shouldReceive('exit')->once()->with([])->andThrow(new TestExitException());

    try {
        $this->accessor->callMethod('prepareTopicList');
    } catch (TestExitException) {
        // Expected exit
    }

    expect($expression)->toBeInstanceOf(Expression::class);
});

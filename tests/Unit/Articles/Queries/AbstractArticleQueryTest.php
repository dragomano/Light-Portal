<?php

declare(strict_types=1);

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use LightPortal\Articles\Queries\AbstractArticleQuery;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use Tests\ReflectionAccessor;

beforeEach(function() {
    $this->sqlMock    = mock(PortalSqlInterface::class);
    $this->eventsMock = mock(EventDispatcherInterface::class);

    $this->query = new class($this->sqlMock, $this->eventsMock) extends AbstractArticleQuery {
        protected function buildDataSelect(): Select
        {
            return $this->sql->select()->from('test_table');
        }

        protected function buildCountSelect(): Select
        {
            return $this->sql->select()->from('test_table')->columns(['count' => 'COUNT(*)']);
        }

        protected function applyBaseConditions(Select $select): void
        {
        }

        protected function getOrders(): array
        {
            return ['created;desc' => 'test'];
        }

        protected function getEventHook(): PortalHook
        {
            return PortalHook::frontBoards;
        }
    };
});

it('returns default sorting when getSorting is called', function () {
    expect($this->query->getSorting())->toBe('created;desc');
});

it('sets custom sorting when setSorting is called with valid sort type', function () {
    $this->eventsMock->shouldReceive('dispatch')->once();

    $this->query->init([]);

    $accessor = new ReflectionAccessor($this->query);
    $accessor->setProperty('orders', ['custom' => 'custom_order']);

    $this->query->setSorting('custom');

    expect($this->query->getSorting())->toBe('custom');
});

it('applies columns correctly when applyColumns is called', function () {
    $select = new Select();

    $accessor = new ReflectionAccessor($this->query);
    $accessor->setProperty('columns', [
        'column1',
        'column2, column3',
        ['column4', 'column5']
    ]);

    $accessor->callMethod('applyColumns', [$select]);

    $rawState = $select->getRawState(Select::COLUMNS);

    expect($rawState)->toContain('column1')
        ->and($rawState)->toContain('column2')
        ->and($rawState)->toContain('column3')
        ->and($rawState)->toContain('column4')
        ->and($rawState)->toContain('column5');
});

it('applies joins correctly when applyJoins is called', function () {
    $select = new Select();

    $accessor = new ReflectionAccessor($this->query);

    $joinCalled = false;
    $accessor->setProperty('joins', [
        function ($sel) use (&$joinCalled) {
            $joinCalled = true;
            $sel->join('test_join', 'condition');
        }
    ]);

    $accessor->callMethod('applyJoins', [$select]);

    expect($joinCalled)->toBeTrue();
});

it('applies wheres correctly when applyWheres is called', function () {
    $select = new Select();

    $accessor = new ReflectionAccessor($this->query);

    $whereCalled = false;
    $accessor->setProperty('wheres', [
        'simple_condition',
        function ($sel) use (&$whereCalled) {
            $whereCalled = true;
            $sel->where('complex_condition');
        }
    ]);

    $accessor->callMethod('applyWheres', [$select]);

    expect($whereCalled)->toBeTrue();
});

it('returns correct orders array when getOrders is called', function () {
    $accessor = new ReflectionAccessor($this->query);
    $orders = $accessor->callMethod('getOrders');

    expect($orders)->toBeArray()
        ->and($orders)->toHaveKey('created;desc')
        ->and($orders['created;desc'])->toBe('test');
});

it('returns correct event hook when getEventHook is called', function () {
    $accessor = new ReflectionAccessor($this->query);
    $hook = $accessor->callMethod('getEventHook');

    expect($hook)->toBeInstanceOf(PortalHook::class)
        ->and($hook)->toBe(PortalHook::frontBoards);
});

it('applies base conditions correctly when applyBaseConditions is called', function () {
    $select = new Select();

    $query = new class($this->sqlMock, $this->eventsMock) extends AbstractArticleQuery {
        protected function buildDataSelect(): Select
        {
            return $this->sql->select()->from('test_table');
        }

        protected function buildCountSelect(): Select
        {
            return $this->sql->select()->from('test_table')->columns(['count' => 'COUNT(*)']);
        }

        protected function applyBaseConditions(Select $select): void
        {
            $select->where('status = ?', 'active');
        }

        protected function getOrders(): array
        {
            return ['created;desc' => 'test'];
        }

        protected function getEventHook(): PortalHook
        {
            return PortalHook::frontBoards;
        }
    };

    $accessor = new ReflectionAccessor($query);
    $accessor->callMethod('applyBaseConditions', [$select]);

    $rawState = $select->getRawState(Select::WHERE);
    expect($rawState)->toBeInstanceOf(Where::class);

    $predicates = $rawState->getPredicates();
    expect($predicates)->toBeArray()
        ->and($predicates)->toHaveCount(1);

    $predicate = $predicates[0][1];
    expect($predicate)->toBeInstanceOf(Expression::class)
        ->and($predicate->getExpression())->toBe('status = ?');
});

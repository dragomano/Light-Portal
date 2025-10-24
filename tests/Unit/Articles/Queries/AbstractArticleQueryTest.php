<?php

declare(strict_types=1);

use Laminas\Db\Sql\Select;
use LightPortal\Articles\Queries\AbstractArticleQuery;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;
use Tests\ReflectionAccessor;

beforeEach(function() {
    $this->prophet = new Prophet();

    $sqlProphecy = $this->prophet->prophesize(PortalSqlInterface::class);
    $this->sqlMock = $sqlProphecy->reveal();

    $eventsProphecy = $this->prophet->prophesize(EventDispatcherInterface::class);
    $this->eventsMock = $eventsProphecy->reveal();

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
            // Empty implementation for testing
        }
    };
});

it('returns default sorting when getSorting is called', function () {
    expect($this->query->getSorting())->toBe('created;desc');
});

it('sets custom sorting when setSorting is called with valid sort type', function () {
    $this->query->init([]);

    // Manually set orders for testing
    $accessor = new ReflectionAccessor($this->query);
    $accessor->setProtectedProperty('orders', ['custom' => 'custom_order']);

    $this->query->setSorting('custom');

    expect($this->query->getSorting())->toBe('custom');
});

it('applies columns correctly when applyColumns is called', function () {
    $select = new Select();

    // Manually set columns
    $accessor = new ReflectionAccessor($this->query);
    $accessor->setProtectedProperty('columns', [
        'column1',
        'column2, column3',
        ['column4', 'column5']
    ]);

    $accessor->callProtectedMethod('applyColumns', [$select]);

    $rawState = $select->getRawState(Select::COLUMNS);

    expect($rawState)->toContain('column1')
        ->and($rawState)->toContain('column2')
        ->and($rawState)->toContain('column3')
        ->and($rawState)->toContain('column4')
        ->and($rawState)->toContain('column5');
});

it('applies joins correctly when applyJoins is called', function () {
    $select = new Select();

    // Manually set joins
    $accessor = new ReflectionAccessor($this->query);

    $joinCalled = false;
    $accessor->setProtectedProperty('joins', [
        function ($sel) use (&$joinCalled) {
            $joinCalled = true;
            $sel->join('test_join', 'condition');
        }
    ]);

    $accessor->callProtectedMethod('applyJoins', [$select]);

    expect($joinCalled)->toBeTrue();
});

it('applies wheres correctly when applyWheres is called', function () {
    $select = new Select();

    // Manually set wheres
    $accessor = new ReflectionAccessor($this->query);

    $whereCalled = false;
    $accessor->setProtectedProperty('wheres', [
        'simple_condition',
        function ($sel) use (&$whereCalled) {
            $whereCalled = true;
            $sel->where('complex_condition');
        }
    ]);

    $accessor->callProtectedMethod('applyWheres', [$select]);

    // Check if wheres were applied
    expect($whereCalled)->toBeTrue();
});

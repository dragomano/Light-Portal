<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasTransactions;
use LightPortal\Utils\ErrorHandler;
use LightPortal\Utils\ErrorHandlerInterface;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::TAGS->value)->execute();

    $this->sql = new PortalSql($adapter);
    $this->errorHandler = new ErrorHandler();

    $this->testClass = new class($this->sql, $this->errorHandler) {
        use HasTransactions;

        public string $entity = 'test_entity';

        public PortalSqlInterface $sql;

        public ErrorHandlerInterface $errorHandler;

        public function __construct(PortalSqlInterface $sql, ErrorHandlerInterface $errorHandler)
        {
            $this->sql = $sql;
            $this->errorHandler = $errorHandler;
        }

        public function cache(): MockInterface|LegacyMockInterface|null
        {
            return mock()->shouldReceive('flush')->once()->getMock();
        }

        public function testStartTransaction(array $items): void
        {
            $this->startTransaction($items);
        }

        public function testFinishTransaction(): void
        {
            $this->finishTransaction();
        }
    };
});

describe('startTransaction', function () {
    it('begins transaction and sets context', function () {
        $items = [['id' => 1], ['id' => 2]];

        $this->testClass->testStartTransaction($items);

        expect(Utils::$context['import_successful'])->toBe(2);
    });
});

describe('finishTransaction', function () {
    it('commits when results exist', function () {
        Lang::$txt['lp_test_entity_set'] = '{test_entity, plural, one {# test entity} other {# test entities}}';

        $this->testClass->testStartTransaction([['id' => 1], ['id' => 2]]);
        $this->testClass->testFinishTransaction();

        expect(Utils::$context['import_successful'])->toBe('Imported: 2 test entities');
    });

    it('rolls back when no results', function () {
        Utils::$context['import_successful'] = 0;

        expect(fn() => $this->testClass->testFinishTransaction())->toThrow(Exception::class);
    });

    it('flushes cache', function () {
        Utils::$context['import_successful'] = 1;

        Lang::$txt['lp_test_entity_set'] = '{test_entity, plural, one {# test entity} other {# test entities}}';

        $this->testClass->testStartTransaction([['id' => 1]]);
        $this->testClass->testFinishTransaction();
    });
});

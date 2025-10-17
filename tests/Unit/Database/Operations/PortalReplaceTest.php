<?php declare(strict_types=1);

use Bugo\LightPortal\Database\Operations\PortalReplace;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Tests\ReflectionAccessor;

describe('PortalReplace', function () {
    beforeEach(function () {
        $this->platform = mock(PlatformInterface::class);
        $this->adapter = mock(AdapterInterface::class);
        $this->result = mock(ResultInterface::class);

        $this->adapter->shouldReceive('getPlatform')->andReturn($this->platform);

        $this->replace = new PortalReplace();
    });

    describe('inheritance from PortalInsert', function () {
        it('inherits PortalInsert functionality', function () {
            expect($this->replace)->toBeInstanceOf(PortalReplace::class);

            // Test that we can use PortalInsert methods
            $result = $this->replace->into('test_table')->columns(['id'])->values([1]);

            expect($result)->toBe($this->replace);
        });

        it('supports table prefixes', function () {
            $replace = new PortalReplace('test', 'prefix_');

            $reflection = new ReflectionAccessor($replace);
            $tableProperty = $reflection->getProtectedProperty('table');

            expect($tableProperty)->toBe('prefix_test');
        });
    });

    describe('setConflictKeys', function () {
        it('sets conflict keys and returns self', function () {
            $keys = ['id', 'name'];
            $result = $this->replace->setConflictKeys($keys);

            expect($result)->toBe($this->replace);
        });

        it('sets empty array of conflict keys', function () {
            $result = $this->replace->setConflictKeys([]);

            expect($result)->toBe($this->replace);
        });

        it('sets single conflict key', function () {
            $result = $this->replace->setConflictKeys(['id']);

            expect($result)->toBe($this->replace);
        });
    });

    describe('executeReplace', function () {
        it('executes REPLACE INTO for MySQL', function () {
            $this->replace->into('test_table');
            $this->replace->columns(['id', 'name', 'email']);
            $this->replace->values([1, 'John Doe', 'john@example.com']);

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('`test_table`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('`name`');
            $this->platform->shouldReceive('quoteIdentifier')->with('email')->andReturn('`email`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(
                    /** @lang text */ 'REPLACE INTO `test_table` (`id`,`name`,`email`) VALUES (?,?,?)',
                    [1, 'John Doe', 'john@example.com']
                )
                ->andReturn($this->result);

            $result = $this->replace->executeReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes REPLACE INTO for SQLite', function () {
            $this->replace->into('test_table');
            $this->replace->columns(['id', 'name', 'email']);
            $this->replace->values([1, 'John Doe', 'john@example.com']);

            $this->platform->shouldReceive('getName')->andReturn('SQLite');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('"test_table"');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('"id"');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('"name"');
            $this->platform->shouldReceive('quoteIdentifier')->with('email')->andReturn('"email"');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(
                    /** @lang text */ 'REPLACE INTO "test_table" ("id","name","email") VALUES (?,?,?)',
                    [1, 'John Doe', 'john@example.com']
                )
                ->andReturn($this->result);

            $result = $this->replace->executeReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes UPSERT for PostgreSQL', function () {
            $this->replace->into('test_table');
            $this->replace->columns(['id', 'name', 'email']);
            $this->replace->values([1, 'John Doe', 'john@example.com']);

            $this->platform->shouldReceive('getName')->andReturn('PostgreSQL');
            $this->platform->shouldReceive('quoteIdentifier')->andReturnUsing(function ($identifier) {
                return '"' . $identifier . '"';
            });

            $this->adapter->shouldReceive('query')
                ->once()
                ->andReturnUsing(function ($sql, $params) {
                    expect($sql)->toContain(/** @lang text */ 'INSERT INTO "test_table"')
                        ->and($sql)->toContain('VALUES (?,?,?)')
                        ->and($sql)->toContain('ON CONFLICT ("id") DO UPDATE SET')
                        ->and($params)->toBe([1, 'John Doe', 'john@example.com']);

                    return $this->result;
                });

            $result = $this->replace->executeReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes UPSERT for PostgreSQL with custom conflict keys', function () {
            $this->replace->into('test_table');
            $this->replace->columns(['id', 'name', 'email']);
            $this->replace->values([1, 'John Doe', 'john@example.com']);
            $this->replace->setConflictKeys(['name', 'email']);

            $this->platform->shouldReceive('getName')->andReturn('PostgreSQL');
            $this->platform->shouldReceive('quoteIdentifier')->andReturnUsing(function ($identifier) {
                return '"' . $identifier . '"';
            });

            $this->adapter->shouldReceive('query')
                ->once()
                ->andReturnUsing(function ($sql, $params) {
                    expect($sql)->toContain(/** @lang text */ 'INSERT INTO "test_table"')
                        ->and($sql)->toContain('VALUES (?,?,?)')
                        ->and($sql)->toContain('ON CONFLICT ("name","email") DO UPDATE SET')
                        ->and($params)->toBe([1, 'John Doe', 'john@example.com']);

                    return $this->result;
                });

            $result = $this->replace->executeReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('throws exception for unsupported platform', function () {
            $this->replace->into('test_table');
            $this->replace->columns(['id', 'name', 'email']);
            $this->replace->values([1, 'John Doe', 'john@example.com']);

            $this->platform->shouldReceive('getName')->andReturn('Oracle');

            expect(fn() => $this->replace->executeReplace($this->adapter))
                ->toThrow(
                    RuntimeException::class,
                    'REPLACE operation not supported for platform: Oracle'
                );
        });
    });

    describe('executeBatchReplace', function () {
        it('returns empty result for empty batch array', function () {
            $replace = new PortalReplace();
            $replace->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('MySQL');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with('SELECT 1 WHERE 0 = 1', [])
                ->andReturn($this->result);

            $result = $replace->batch([])->executeBatchReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes batch REPLACE INTO for MySQL', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ];
            $this->replace->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('`test_table`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('`name`');
            $this->platform->shouldReceive('quoteIdentifier')->with('email')->andReturn('`email`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(
                    /** @lang text */ 'REPLACE INTO `test_table` (`id`,`name`,`email`) VALUES (?,?,?),(?,?,?)',
                    [1, 'John Doe', 'john@example.com', 2, 'Jane Doe', 'jane@example.com']
                )
                ->andReturn($this->result);

            $result = $this->replace->batch($batchData)->executeBatchReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes batch UPSERT for PostgreSQL', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ];
            $this->replace->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('PostgreSQL');
            $this->platform->shouldReceive('quoteIdentifier')->andReturnUsing(function ($identifier) {
                return '"' . $identifier . '"';
            });

            $this->adapter->shouldReceive('query')
                ->once()
                ->andReturnUsing(function ($sql, $params) {
                    expect($sql)->toContain(/** @lang text */ 'INSERT INTO "test_table"')
                        ->and($sql)->toContain('VALUES (?,?,?),(?,?,?)')
                        ->and($sql)->toContain('ON CONFLICT ("id") DO UPDATE SET')
                        ->and($params)->toBe([1, 'John Doe', 'john@example.com', 2, 'Jane Doe', 'jane@example.com']);

                    return $this->result;
                });

            $result = $this->replace->batch($batchData)->executeBatchReplace($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('throws exception for unsupported platform in batch mode', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ];
            $this->replace->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('Oracle');

            expect(fn() => $this->replace->batch($batchData)->executeBatchReplace($this->adapter))
                ->toThrow(
                    RuntimeException::class,
                    'Batch REPLACE operation not supported for platform: Oracle'
                );
        });
    });

    describe('PortalInsert methods integration', function () {
        it('uses into() method correctly', function () {
            $result = $this->replace->into('test_table');

            expect($result)->toBe($this->replace);

            $reflection = new ReflectionAccessor($this->replace);
            $tableProperty = $reflection->getProtectedProperty('table');

            expect($tableProperty)->toBe('test_table');
        });

        it('uses columns() method correctly', function () {
            $columns = ['id', 'name', 'email'];
            $result = $this->replace->columns($columns);

            expect($result)->toBe($this->replace);

            $rawState = $this->replace->getRawState();
            expect($rawState['columns'])->toBe($columns);
        });

        it('uses values() method correctly', function () {
            $columns = ['id', 'name', 'email'];
            $values = [1, 'John Doe', 'john@example.com'];

            $result = $this->replace->columns($columns)->values($values);

            expect($result)->toBe($this->replace);

            $reflection = new ReflectionAccessor($this->replace);

            expect($reflection->callProtectedMethod('getValues'))->toBe($values);
        });

        it('uses batch() method correctly', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ];

            $result = $this->replace->batch($batchData);

            expect($result)->toBe($this->replace);

            $reflection = new ReflectionAccessor($this->replace);
            $batchValuesProperty = $reflection->getProtectedProperty('batchValues');

            expect($batchValuesProperty)->toBe($batchData)
                ->and($this->replace->isBatch())->toBeTrue();
        });

        it('isBatch() method works correctly', function () {
            expect($this->replace->isBatch())->toBeFalse();

            $this->replace->batch([['id' => 1]]);
            expect($this->replace->isBatch())->toBeTrue();
        });
    });

    describe('table prefix functionality', function () {
        it('applies prefix to table name in executeReplace', function () {
            $replace = new PortalReplace('test', 'prefix_');
            $replace->into('users')->columns(['id'])->values([1]);

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('prefix_users')->andReturn('`prefix_users`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'REPLACE INTO `prefix_users` (`id`) VALUES (?)', [1])
                ->andReturn($this->result);

            $replace->executeReplace($this->adapter);
        });

        it('applies prefix to table name in executeBatchReplace', function () {
            $replace = new PortalReplace('users', 'prefix_');
            $batchData = [['id' => 1, 'name' => 'John']];
            $replace->batch($batchData);

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('prefix_users')->andReturn('`prefix_users`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('`name`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'REPLACE INTO `prefix_users` (`id`,`name`) VALUES (?,?)', [1, 'John'])
                ->andReturn($this->result);

            $replace->executeBatchReplace($this->adapter);
        });
    });

    describe('edge cases and error handling', function () {
        it('handles null values in executeReplace', function () {
            $this->replace->into('test_table')->columns(['id', 'name'])->values([1, null]);

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('`test_table`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('`name`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'REPLACE INTO `test_table` (`id`,`name`) VALUES (?,?)', [1, null])
                ->andReturn($this->result);

            $this->replace->executeReplace($this->adapter);
        });

        it('handles batch with single row', function () {
            $batchData = [['id' => 1, 'name' => 'John']];
            $this->replace->into('test_table')->batch($batchData);

            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('`test_table`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');
            $this->platform->shouldReceive('quoteIdentifier')->with('name')->andReturn('`name`');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'REPLACE INTO `test_table` (`id`,`name`) VALUES (?,?)', [1, 'John'])
                ->andReturn($this->result);

            $this->replace->executeBatchReplace($this->adapter);
        });

        it('throws exception when no table specified', function () {
            $this->platform->shouldReceive('getName')->andReturn('MySQL');

            expect(fn() => $this->replace->columns(['id'])->values([1])->executeReplace($this->adapter))
                ->toThrow(Exception::class);
        });


        it('throws exception when no values specified', function () {
            $this->replace->into('test_table')->columns(['id']);
            $this->platform->shouldReceive('getName')->andReturn('MySQL');
            $this->platform->shouldReceive('quoteIdentifier')->with('test_table')->andReturn('`test_table`');
            $this->platform->shouldReceive('quoteIdentifier')->with('id')->andReturn('`id`');

            expect(fn() => $this->replace->executeReplace($this->adapter))
                ->toThrow(Exception::class);
        });
    });
});

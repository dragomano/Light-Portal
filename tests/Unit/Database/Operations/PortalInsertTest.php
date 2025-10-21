<?php declare(strict_types=1);

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use LightPortal\Database\Operations\PortalInsert;
use Tests\ReflectionAccessor;

describe('PortalInsert', function () {
    beforeEach(function () {
        $this->platform = mock(PlatformInterface::class);
        $this->adapter = mock(AdapterInterface::class);
        $this->adapter->shouldReceive('getPlatform')->andReturn($this->platform);
        $this->result = mock(ResultInterface::class);

        $this->insert = new PortalInsert();
    });

    describe('base functionality', function () {
        it('constructs with prefix', function () {
            $insert = new PortalInsert('test', 'prefix_');

            expect($insert)->toBeInstanceOf(PortalInsert::class);
        });

        it('adds prefix to string table in into', function () {
            $insert = new PortalInsert('test', 'prefix_');

            $result = $insert->into('users');

            expect($result)->toBeInstanceOf(PortalInsert::class);

            $reflection = new ReflectionAccessor($insert);
            $tableProperty = $reflection->getProtectedProperty('table');

            expect($tableProperty)->toBe('prefix_users');
        });

        it('does not add prefix when prefix is empty', function () {
            $insert = new PortalInsert('test', '');

            $insert->into('users');

            $reflection = new ReflectionAccessor($insert);
            $tableProperty = $reflection->getProtectedProperty('table');

            expect($tableProperty)->toBe('users');
        });
    });

    describe('executeBatchInsert', function () {
        it('returns empty result for empty batch array', function () {
            $insert = new PortalInsert();
            $insert->into('test_table');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with('SELECT 1 WHERE 0 = 1', [])
                ->andReturn($this->result);

            $result = $insert->batch([])->executeBatch($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes batch INSERT for MySQL', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ];
            $this->insert->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('MySQL');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'INSERT INTO test_table (id,name,email) VALUES (?,?,?),(?,?,?)', [1, 'John Doe', 'john@example.com', 2, 'Jane Doe', 'jane@example.com'])
                ->andReturn($this->result);

            $result = $this->insert->batch($batchData)->executeBatch($this->adapter);

            expect($result)->toBe($this->result);
        });

        it('executes batch INSERT for PostgreSQL', function () {
            $batchData = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ];
            $this->insert->into('test_table');

            $this->platform->shouldReceive('getName')->andReturn('PostgreSQL');

            $this->adapter->shouldReceive('query')
                ->once()
                ->with(/** @lang text */ 'INSERT INTO test_table (id,name,email) VALUES (?,?,?),(?,?,?)', [1, 'John Doe', 'john@example.com', 2, 'Jane Doe', 'jane@example.com'])
                ->andReturn($this->result);

            $result = $this->insert->batch($batchData)->executeBatch($this->adapter);

            expect($result)->toBe($this->result);
        });
    });
});

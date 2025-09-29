<?php declare(strict_types=1);

use Bugo\LightPortal\Migrations\Operations\PortalSelect;

describe('PortalSelect', function () {
    it('constructs with prefix', function () {
        $select = new PortalSelect(prefix: 'prefix_');

        expect($select)->toBeInstanceOf(PortalSelect::class);
    });

    it('adds prefix to string table in from', function () {
        $select = new PortalSelect(null, 'prefix_');

        $result = $select->from('users');

        expect($result)->toBeInstanceOf(PortalSelect::class);

        $reflection = new ReflectionClass($select);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($select))->toBe('prefix_users');
    });

    it('does not add prefix when prefix is empty', function () {
        $select = new PortalSelect(null, '');

        $select->from('users');

        $reflection = new ReflectionClass($select);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($select))->toBe('users');
    });

    it('adds prefix to string tables in array from', function () {
        $select = new PortalSelect(null, 'prefix_');

        $select->from(['u' => 'users', 'p' => 'posts']);

        $reflection = new ReflectionClass($select);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($select))->toBe(['u' => 'prefix_users', 'p' => 'prefix_posts']);
    });

    it('leaves non-string tables unchanged in array from', function () {
        $select = new PortalSelect(null, 'prefix_');

        $obj = new stdClass();
        $select->from(['u' => 'users', 'o' => $obj]);

        $reflection = new ReflectionClass($select);
        $tableProperty = $reflection->getProperty('table');

        expect($tableProperty->getValue($select))->toBe(['u' => 'prefix_users', 'o' => $obj]);
    });

    it('throws exception for invalid table types', function ($invalidTable) {
        expect(fn() => new PortalSelect($invalidTable, 'prefix_'))->toThrow(InvalidArgumentException::class);
    })->with([
        [123],
        [new stdClass()],
        [true],
        [['table']],
    ]);
});

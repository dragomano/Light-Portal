<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

beforeEach(function () {
    $this->adapter = new Adapter(['driver' => 'Pdo_Sqlite', 'database' => ':memory:']);
    $this->sql = new Sql($this->adapter);
});

dataset('array types', [
    'numeric array' => [
        [0, 1, 2, 3],
        /** @lang text */ 'SELECT "p"."entry_type" AS "entry_type" FROM "posts" AS "p" WHERE "p"."entry_type" IN (:where1, :where2, :where3, :where4)',
        [0, 1, 2, 3],
    ],
    'associative array' => [
        ['draft' => 0, 'published' => 1, 'archived' => 2, 'deleted' => 3],
        /** @lang text */ 'SELECT "p"."entry_type" AS "entry_type" FROM "posts" AS "p" WHERE "p"."entry_type" IN (:where1, :where2, :where3, :where4)',
        [0, 1, 2, 3],
    ],
    'string array' => [
        ['draft', 'published', 'archived', 'deleted'],
        /** @lang text */ 'SELECT "p"."entry_type" AS "entry_type" FROM "posts" AS "p" WHERE "p"."entry_type" IN (:where1, :where2, :where3, :where4)',
        ['draft', 'published', 'archived', 'deleted'],
    ],
    'assoc string array' => [
        ['draft' => 'draft', 'published' => 'published', 'archived' => 'archived', 'deleted' => 'deleted'],
        /** @lang text */ 'SELECT "p"."entry_type" AS "entry_type" FROM "posts" AS "p" WHERE "p"."entry_type" IN (:where1, :where2, :where3, :where4)',
        ['draft', 'published', 'archived', 'deleted'],
    ],
    'empty array' => [
        [],
        /** @lang text */ 'SELECT "p"."entry_type" AS "entry_type" FROM "posts" AS "p" WHERE "p"."entry_type" IN (NULL)',
        [],
    ],
]);

it('parses ->where->in correctly', function (
    array $array, string $expected_sql, array $expected_params
) {
    $select = $this->sql->select(['p' => 'posts']);
    $select->columns(['entry_type']);
    $select->where->in('p.entry_type', $array);

    $statement = $this->sql->prepareStatementForSqlObject($select);
    $actualSql = trim($statement->getSql());
    $params = array_values($statement->getParameterContainer()->getNamedArray());

    expect($actualSql)->toBe($expected_sql)
        ->and($params)->toBe($expected_params);
})->with('array types')->group('db');

it('parses ->where(["col" => $array]) correctly', function (
    array $array, string $expected_sql, array $expected_params
) {
    $select = $this->sql->select(['p' => 'posts']);
    $select->columns(['entry_type']);
    $select->where(['p.entry_type' => $array]);

    $statement = $this->sql->prepareStatementForSqlObject($select);
    $actualSql = trim($statement->getSql());
    $params = array_values($statement->getParameterContainer()->getNamedArray());

    expect($actualSql)->toBe($expected_sql)
        ->and($params)->toBe($expected_params);
})->with('array types')->group('db');

it('associative array ignores keys in IN predicate', function () {
    $assocArray = ['foo' => 0, 'bar' => 1];
    $numericArray = [0, 1];

    $select1 = $this->sql->select(['p' => 'posts']);
    $select1->columns(['entry_type']);
    $select1->where->in('p.entry_type', $assocArray);

    $select2 = $this->sql->select(['p' => 'posts']);
    $select2->columns(['entry_type']);
    $select2->where->in('p.entry_type', $numericArray);

    $statement1 = $this->sql->prepareStatementForSqlObject($select1);
    $statement2 = $this->sql->prepareStatementForSqlObject($select2);

    expect($statement1->getSql())->toBe($statement2->getSql())
        ->and(array_values($statement1->getParameterContainer()->getNamedArray()))->toBe([0, 1])
        ->and(array_values($statement2->getParameterContainer()->getNamedArray()))->toBe([0, 1]);
})->group('db');

it('handles empty array gracefully', function () {
    $emptyArray = [];
    $select = $this->sql->select(['p' => 'posts']);
    $select->columns(['entry_type']);
    $select->where->in('p.entry_type', $emptyArray);

    $statement = $this->sql->prepareStatementForSqlObject($select);
    $actualSql = $statement->getSql();

    expect($actualSql)->toContain('IN (NULL)');
})->group('db');

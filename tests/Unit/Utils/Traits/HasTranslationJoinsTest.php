<?php

declare(strict_types=1);

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\Traits\HasTranslationJoins;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->testClass = new class($this->sql) {
        use HasTranslationJoins;

        public PortalSqlInterface $sql;

        public function __construct(PortalSqlInterface $sql)
        {
            $this->sql = $sql;
        }

        public function callAddTranslationJoins(Select $select, array $config = []): void
        {
            $this->addTranslationJoins($select, $config);
        }

        public function callGetTranslationFilter(
            string $tableAlias = 'p',
            string $idField = 'page_id',
            array $fields = ['title', 'content', 'description'],
            string $entity = 'page'
        )
        {
            return $this->getTranslationFilter($tableAlias, $idField, $fields, $entity);
        }
    };

    $this->reflection = new ReflectionAccessor($this->testClass);
});

it('creates fallback alias correctly', function () {
    $result = $this->reflection->callMethod('getFallbackAlias', ['alias_without_t']);
    expect($result)->toBe('alias_without_tf');

    $result = $this->reflection->callMethod('getFallbackAlias', ['some_alias']);
    expect($result)->toBe('some_aliasf');

    // Test when it ends with 't' - should replace with 'tf'
    $result = $this->reflection->callMethod('getFallbackAlias', ['t']);
    expect($result)->toBe('tf');

    $result = $this->reflection->callMethod('getFallbackAlias', ['alias_t']);
    expect($result)->toBe('alias_tf');

    // Test default alias used in addTranslationJoins
    $result = $this->reflection->callMethod('getFallbackAlias', ['t']);
    expect($result)->toBe('tf');
});

dataset('fallback alias scenarios', [
    'alias ending with t' => ['t', 'tf'],
    'alias_t'             => ['alias_t', 'alias_tf'],
    'alias without t'     => ['alias', 'aliasf'],
    'empty string'        => ['', 'f'],
    'single char a'       => ['a', 'af'],
    'long alias'          => ['very_long_alias', 'very_long_aliasf'],
]);

it('generates fallback aliases correctly', function (string $input, string $expected) {
    $result = $this->reflection->callMethod('getFallbackAlias', [$input]);
    expect($result)->toBe($expected);
})->with('fallback alias scenarios');

it('adds translation joins with custom config', function () {
    $select = new Select('lp_pages');
    $select->columns(['page_id', 'title']);

    $config = [
        'lang'     => 'russian',
        'fallback' => 'english',
        'primary'  => 'custom_id',
        'entity'   => 'custom_entity',
        'fields'   => ['title', 'description'],
        'alias'    => 'custom_alias',
    ];

    $this->testClass->callAddTranslationJoins($select, $config);

    $sql = $select->getSqlString($this->sql->getAdapter()->getPlatform());

    expect($sql)->toContain('custom_alias.item_id = custom_id')
        ->and($sql)->toContain('custom_alias.type')
        ->and($sql)->toContain('custom_alias.lang')
        ->and($sql)->toContain('custom_aliasf.item_id = custom_id')
        ->and($sql)->toContain('custom_aliasf.lang');
});

it('generates translation columns correctly', function () {
    $fields = ['title', 'content'];
    $alias = 't';
    $aliasFallback = 'tf';

    $columns = $this->reflection->callMethod('getTranslationColumns', [$fields, $alias, $aliasFallback]);

    expect($columns)->toHaveKey('title')
        ->and($columns)->toHaveKey('content')
        ->and($columns['title'])->toBeInstanceOf(Expression::class)
        ->and($columns['content'])->toBeInstanceOf(Expression::class);
});

it('handles translation filter creation', function () {
    $filter = $this->testClass->callGetTranslationFilter();

    expect($filter)->toBeInstanceOf(Expression::class);
});

it('gets language query parameters', function () {
    $params = $this->reflection->callMethod('getLangQueryParams');

    expect($params)->toHaveKey('lang')
        ->and($params)->toHaveKey('fallback_lang')
        ->and($params)->toHaveKey('guest');
});

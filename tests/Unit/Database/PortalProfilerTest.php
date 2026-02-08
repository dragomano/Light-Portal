<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\StatementContainerInterface;
use Laminas\Db\Sql\Exception\InvalidArgumentException;
use LightPortal\Database\PortalProfiler;
use Tests\ReflectionAccessor;

describe('PortalProfiler', function () {
    it('constructs with platform', function () {
        $platform = mock(PlatformInterface::class);
        $profiler = new PortalProfiler($platform);

        expect($profiler)->toBeInstanceOf(PortalProfiler::class);
    });

    it('constructs without platform', function () {
        $profiler = new PortalProfiler();

        expect($profiler)->toBeInstanceOf(PortalProfiler::class);
    });

    it('starts profiling with string SQL', function () {
        $profiler = new PortalProfiler();

        $sql = /** @lang text */ 'SELECT * FROM users';

        $result = $profiler->profilerStart($sql);

        expect($result)->toBe($profiler)
            ->and($profiler->getProfiles())->toHaveCount(1);
    });

    it('starts profiling with StatementContainerInterface', function () {
        $platform = mock(PlatformInterface::class);
        $platform->shouldReceive('quoteTrustedValue')->andReturn("'1'");
        $profiler = new PortalProfiler($platform);

        $paramContainer = mock();
        $paramContainer->shouldReceive('getNamedArray')->andReturn(['id' => 1]);

        $statement = mock(StatementContainerInterface::class);
        $statement->shouldReceive('getSql')->andReturn(/** @lang text */ 'SELECT * FROM users WHERE id = :id');
        $statement->shouldReceive('getParameterContainer')->andReturn($paramContainer);

        $result = $profiler->profilerStart($statement);

        expect($result)->toBe($profiler)
            ->and($profiler->getProfiles())->toHaveCount(1);
    });

    it('throws exception for invalid target', function () {
        $profiler = new PortalProfiler();

        expect(fn() => $profiler->profilerStart(123))->toThrow(InvalidArgumentException::class);
    });

    it('returns formatted SQL (simple case)', function () {
        $source = /** @lang text */
            <<<SQL
SELECT smf_lp_plugins.name AS name, smf_lp_plugins.config AS config, smf_lp_plugins.value AS value FROM smf_lp_plugins
SQL;

        $formatted = /** @lang text */
            <<<SQL
SELECT
    smf_lp_plugins.name AS name,
    smf_lp_plugins.config AS config,
    smf_lp_plugins.value AS value
FROM smf_lp_plugins
SQL;

        $accessor = new ReflectionAccessor(new PortalProfiler());
        $result = $accessor->callMethod('formatSql', [$source]);

        expect($result)->toBe($formatted);
    });

    it('returns formatted SQL (complex case)', function () {
        $source = /** @lang text */
            <<<SQL
SELECT b.*, COALESCE(NULLIF(t.title, ""), tf.title, "") AS title, COALESCE(NULLIF(t.content, ""), tf.content, "") AS content, pp.name AS name, pp.value AS value FROM smf_lp_blocks AS b LEFT JOIN smf_lp_translations AS t ON t.item_id = b.block_id AND t.type = 'block' AND t.lang = 'russian' LEFT JOIN smf_lp_translations AS tf ON tf.item_id = b.block_id AND tf.type = 'block' AND tf.lang = 'english' LEFT JOIN smf_lp_params AS pp ON pp.item_id = b.block_id AND pp.type = 'block' WHERE status = '1' ORDER BY placement DESC, priority ASC
SQL;

        $formatted = /** @lang text */
            <<<SQL
SELECT
    b.*,
    COALESCE(NULLIF(t.title, ""), tf.title, "") AS title,
    COALESCE(NULLIF(t.content, ""), tf.content, "") AS content,
    pp.name AS name,
    pp.value AS value
FROM smf_lp_blocks AS b
LEFT JOIN smf_lp_translations AS t ON t.item_id = b.block_id
    AND t.type = 'block'
    AND t.lang = 'russian'
LEFT JOIN smf_lp_translations AS tf ON tf.item_id = b.block_id
    AND tf.type = 'block'
    AND tf.lang = 'english'
LEFT JOIN smf_lp_params AS pp ON pp.item_id = b.block_id
    AND pp.type = 'block'
WHERE status = '1'
ORDER BY placement DESC, priority ASC
SQL;

        $accessor = new ReflectionAccessor(new PortalProfiler());
        $result = $accessor->callMethod('formatSql', [$source]);

        expect($result)->toBe($formatted);
    });

    it('returns formatted SQL (complex case with subqueries)', function () {
        $source = /** @lang text */
            <<<SQL
SELECT p.page_id AS page_id, p.slug AS slug, p.permissions AS permissions, pp2.value AS icon, (SELECT smf_lp_translations.title AS title FROM smf_lp_translations WHERE item_id = p.page_id AND type = 'page' AND lang IN ('russian', 'english') ORDER BY lang = 'russian' DESC LIMIT 1) AS page_title FROM smf_lp_pages AS p LEFT JOIN smf_lp_params AS pp ON pp.item_id = p.page_id AND pp.type = 'page' AND pp.name = 'show_in_menu' LEFT JOIN smf_lp_params AS pp2 ON pp2.item_id = p.page_id AND pp2.type = 'page' AND pp2.name = 'page_icon' WHERE p.status = '1' AND p.deleted_at = '0' AND p.created_at <= '1761586699' AND pp.value = '1' AND p.entry_type IN ('default', 'internal') AND EXISTS (SELECT 1 FROM smf_lp_translations WHERE item_id = p.page_id AND type = 'page' AND lang IN ('russian', 'english') AND (title IS NOT NULL AND title != ''))
SQL;

        $formatted = /** @lang text */
            <<<SQL
SELECT
    p.page_id AS page_id,
    p.slug AS slug,
    p.permissions AS permissions,
    pp2.value AS icon,
    (
        SELECT smf_lp_translations.title AS title
        FROM smf_lp_translations
        WHERE item_id = p.page_id
            AND type = 'page'
            AND lang IN ('russian', 'english')
        ORDER BY lang = 'russian' DESC
        LIMIT 1
    ) AS page_title
FROM smf_lp_pages AS p
LEFT JOIN smf_lp_params AS pp ON pp.item_id = p.page_id
    AND pp.type = 'page'
    AND pp.name = 'show_in_menu'
LEFT JOIN smf_lp_params AS pp2 ON pp2.item_id = p.page_id
    AND pp2.type = 'page'
    AND pp2.name = 'page_icon'
WHERE p.status = '1'
    AND p.deleted_at = '0'
    AND p.created_at <= '1761586699'
    AND pp.value = '1'
    AND p.entry_type IN ('default', 'internal')
    AND EXISTS (
        SELECT 1
        FROM smf_lp_translations
        WHERE item_id = p.page_id
            AND type = 'page'
            AND lang IN ('russian', 'english')
            AND (title IS NOT NULL AND title != '')
    )
SQL;

        $accessor = new ReflectionAccessor(new PortalProfiler());
        $result = $accessor->callMethod('formatSql', [$source]);

        expect($result)->toBe($formatted);
    });
});

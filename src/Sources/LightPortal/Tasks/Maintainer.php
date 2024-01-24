<?php declare(strict_types=1);

/**
 * Maintainer.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Tasks;

use Bugo\LightPortal\Utils\Utils;

final class Maintainer extends BackgroundTask
{
	public function execute(): bool
	{
		@ini_set('opcache.enable', '0');

		$this->removeRedundantValues();
		$this->updateNumComments();
		$this->updateLastCommentIds();
		$this->optimizeTables();

		return (bool) Utils::$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			[
				'task_file'    => 'string-255',
				'task_class'   => 'string-255',
				'task_data'    => 'string',
				'claimed_time' => 'int'
			],
			[
				'$sourcedir/LightPortal/Tasks/Maintainer.php',
				'\\' . self::class,
				'',
				time() + (7 * 24 * 60 * 60)
			],
			['id_task'],
			1
		);
	}

	private function removeRedundantValues(): void
	{
		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE value = {string:empty_value}',
			[
				'empty_value' => ''
			]
		);

		$value = Utils::$smcFunc['db_title'] === POSTGRE_TITLE ? "string_agg(value, ',')" : 'GROUP_CONCAT(value)';

		$result = Utils::$smcFunc['db_query']('', '
			SELECT ' . $value . ' AS value
			FROM {db_prefix}lp_params
			WHERE type = {literal:page}
				AND name = {literal:keywords}',
			[]
		);

		[$usedTags] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		if ($usedTags) {
			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_tags
				WHERE tag_id NOT IN ({array_int:tags})',
				[
					'tags' => explode(',', $usedTags)
				]
			);
		}

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE title = {string:empty_value}',
			[
				'empty_value' => ''
			]
		);

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT id FROM {db_prefix}lp_comments
			WHERE parent_id <> 0
				AND parent_id NOT IN (SELECT * FROM (SELECT id FROM {db_prefix}lp_comments) com)',
			[]
		);

		$comments = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$comments[] = $row['id'];
		}

		Utils::$smcFunc['db_free_result']($result);

		if ($comments) {
			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_comments
				WHERE id IN ({array_int:items})',
				[
					'items' => $comments,
				]
			);

			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_params
				WHERE item_id IN ({array_int:items})
					AND type = {literal:comment}',
				[
					'items' => $comments,
				]
			);
		}
	}

	private function updateNumComments(): void
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT p.page_id, COUNT(c.id) AS amount
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
			[]
		);

		$pages = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
			$pages[$row['page_id']] = $row['amount'];

		Utils::$smcFunc['db_free_result']($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $page_id => $num_comments)
			$line .= ' WHEN page_id = ' . $page_id . ' THEN ' . $num_comments;

		Utils::$smcFunc['db_query']('', /** @lang text */ '
			UPDATE {db_prefix}lp_pages
			SET num_comments = CASE ' . $line . '
				ELSE num_comments
				END
			WHERE page_id IN ({array_int:pages})',
			[
				'pages' => array_keys($pages)
			]
		);
	}

	private function updateLastCommentIds(): void
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT p.page_id, MAX(c.id) AS last_comment_id
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
			[]
		);

		$pages = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result))
			$pages[$row['page_id']] = $row['last_comment_id'] ?? 0;

		Utils::$smcFunc['db_free_result']($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $page_id => $last_comment_id)
			$line .= ' WHEN page_id = ' . $page_id . ' THEN ' . $last_comment_id;

		Utils::$smcFunc['db_query']('', /** @lang text */ '
			UPDATE {db_prefix}lp_pages
			SET last_comment_id = CASE ' . $line . '
				ELSE last_comment_id
				END
			WHERE page_id IN ({array_int:pages})',
			[
				'pages' => array_keys($pages)
			]
		);
	}

	private function optimizeTables(): void
	{
		$this->dbExtend();

		array_map(
			fn($table) => Utils::$smcFunc['db_optimize_table']('{db_prefix}' . $table),
			[
				'lp_blocks',
				'lp_categories',
				'lp_comments',
				'lp_pages',
				'lp_params',
				'lp_plugins',
				'lp_tags',
				'lp_titles',
			]
		);
	}
}

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
 * @version 2.4
 */

namespace Bugo\LightPortal\Tasks;

final class Maintainer extends BackgroundTask
{
	public function execute(): bool
	{
		@ini_set('opcache.enable', '0');

		$this->removeRedundantValues();
		$this->updateNumComments();
		$this->updateLastCommentIds();
		$this->optimizeTables();

		return (bool) $this->smcFunc['db_insert']('insert',
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

	private function removeRedundantValues()
	{
		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE value = {string:empty_value}',
			[
				'empty_value' => ''
			]
		);

		$value = $this->smcFunc['db_title'] === POSTGRE_TITLE ? "string_agg(value, ',')" : 'GROUP_CONCAT(value)';

		$result = $this->smcFunc['db_query']('', '
			SELECT ' . $value . ' AS value
			FROM {db_prefix}lp_params
			WHERE type = {literal:page}
				AND name = {literal:keywords}',
			[]
		);

		[$usedTags] = $this->smcFunc['db_fetch_row']($result);
		$this->smcFunc['db_free_result']($result);

		if ($usedTags) {
			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_tags
				WHERE tag_id NOT IN ({array_int:tags})',
				[
					'tags' => explode(',', $usedTags)
				]
			);
		}

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE title = {string:empty_value}',
			[
				'empty_value' => ''
			]
		);

		$result = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT id FROM {db_prefix}lp_comments
			WHERE parent_id <> 0
				AND parent_id NOT IN (SELECT * FROM (SELECT id FROM {db_prefix}lp_comments) com)',
			[]
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$comments[] = $row['id'];
		}

		$this->smcFunc['db_free_result']($result);

		if ($comments) {
			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_comments
				WHERE id IN ({array_int:items})',
				[
					'items' => $comments,
				]
			);

			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_params
				WHERE item_id IN ({array_int:items})
					AND type = {literal:comment}',
				[
					'items' => $comments,
				]
			);
		}
	}

	private function updateNumComments()
	{
		$result = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT p.page_id, COUNT(c.id) AS amount
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
			[]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result))
			$pages[$row['page_id']] = $row['amount'];

		$this->smcFunc['db_free_result']($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $page_id => $num_comments)
			$line .= ' WHEN page_id = ' . $page_id . ' THEN ' . $num_comments;

		$this->smcFunc['db_query']('', /** @lang text */ '
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

	private function updateLastCommentIds()
	{
		$result = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT p.page_id, MAX(c.id) AS last_comment_id
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
			[]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result))
			$pages[$row['page_id']] = $row['last_comment_id'] ?? 0;

		$this->smcFunc['db_free_result']($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $page_id => $last_comment_id)
			$line .= ' WHEN page_id = ' . $page_id . ' THEN ' . $last_comment_id;

		$this->smcFunc['db_query']('', /** @lang text */ '
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

	private function optimizeTables()
	{
		$tables = [
			'lp_blocks',
			'lp_categories',
			'lp_comments',
			'lp_pages',
			'lp_params',
			'lp_plugins',
			'lp_tags',
			'lp_titles'
		];

		$this->dbExtend();

		foreach ($tables as $table)
			$this->smcFunc['db_optimize_table']('{db_prefix}' . $table);
	}
}

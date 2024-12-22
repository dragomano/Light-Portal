<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Tasks;

use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\Compat\Db;

use function array_keys;
use function array_map;
use function ini_set;
use function time;

final class Maintainer extends BackgroundTask
{
	public function execute(): bool
	{
		@ini_set('opcache.enable', '0');

		$this->removeRedundantValues();
		$this->updateNumComments();
		$this->updateLastCommentIds();
		$this->optimizeTables();

		return (bool) Db::$db->insert('insert',
			'{db_prefix}background_tasks',
			[
				'task_file'    => 'string-255',
				'task_class'   => 'string-255',
				'task_data'    => 'string',
				'claimed_time' => 'int',
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
		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE value = {string:empty_value}',
			[
				'empty_value' => '',
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE value = {string:empty_value}',
			[
				'empty_value' => '',
			]
		);

		$result = Db::$db->query('', /** @lang text */ '
			SELECT id FROM {db_prefix}lp_comments
			WHERE parent_id <> 0
				AND parent_id NOT IN (SELECT * FROM (SELECT id FROM {db_prefix}lp_comments) com)',
		);

		$commentRepository = app('comment_repo');
		$commentRepository->removeFromResult($result);
	}

	private function updateNumComments(): void
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT p.page_id, COUNT(c.id) AS amount
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$pages[$row['page_id']] = $row['amount'];
		}

		Db::$db->free_result($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $pageId => $commentsCount) {
			$line .= ' WHEN page_id = ' . $pageId . ' THEN ' . $commentsCount;
		}

		Db::$db->query('', /** @lang text */ '
			UPDATE {db_prefix}lp_pages
			SET num_comments = CASE ' . $line . '
				ELSE num_comments
				END
			WHERE page_id IN ({array_int:pages})',
			[
				'pages' => array_keys($pages),
			]
		);
	}

	private function updateLastCommentIds(): void
	{
		$result = Db::$db->query('', /** @lang text */ '
			SELECT p.page_id, MAX(c.id) AS last_comment_id
			FROM {db_prefix}lp_pages p
				LEFT JOIN {db_prefix}lp_comments c ON (c.page_id = p.page_id)
			GROUP BY p.page_id
			ORDER BY p.page_id',
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$pages[$row['page_id']] = $row['last_comment_id'] ?? 0;
		}

		Db::$db->free_result($result);

		if (empty($pages))
			return;

		$line = '';
		foreach ($pages as $pageId => $lastCommentId) {
			$line .= ' WHEN page_id = ' . $pageId . ' THEN ' . $lastCommentId;
		}

		Db::$db->query('', /** @lang text */ '
			UPDATE {db_prefix}lp_pages
			SET last_comment_id = CASE ' . $line . '
				ELSE last_comment_id
				END
			WHERE page_id IN ({array_int:pages})',
			[
				'pages' => array_keys($pages),
			]
		);
	}

	private function optimizeTables(): void
	{
		array_map(
			static fn($table) => Db::$db->optimize_table('{db_prefix}' . $table),
			[
				'lp_blocks',
				'lp_categories',
				'lp_comments',
				'lp_page_tag',
				'lp_pages',
				'lp_params',
				'lp_plugins',
				'lp_tags',
				'lp_titles',
			]
		);
	}
}

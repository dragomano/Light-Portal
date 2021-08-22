<?php

namespace Bugo\LightPortal;

/**
 * Task.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

class Task extends \SMF_BackgroundTask
{
	/**
	 * @return bool
	 */
	public function execute(): bool
	{
		global $smcFunc;

		@ini_set('opcache.enable', '0');

		$this->removeRedundantValues();
		$this->optimizeTables();

		$next_time = time() + (7 * 24 * 60 * 60);

		// Add a background task for next update | Добавляем фоновую задачу для следующего обновления
		$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array('task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string', 'claimed_time' => 'int'),
			array('$sourcedir/LightPortal/Task.php', '\Bugo\LightPortal\Task', '', $next_time),
			array('id_task')
		);

		return true;
	}

	/**
	 * @return void
	 */
	private function removeRedundantValues()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE value = {string:empty_value}',
			array(
				'empty_value' => ''
			)
		);

		$request = $smcFunc['db_query']('', '
			SELECT GROUP_CONCAT(value) AS value FROM {db_prefix}lp_params WHERE type = {literal:page} AND name = {literal:keywords}',
			array()
		);

		[$usedTags] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($usedTags)) {
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_tags
				WHERE tag_id NOT IN ({array_int:tags})',
				array(
					'tags' => explode(',', $usedTags)
				)
			);
		}

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE title = {string:empty_value}',
			array(
				'empty_value' => ''
			)
		);
	}

	/**
	 * @return void
	 */
	private function optimizeTables()
	{
		global $smcFunc;

		$tables = [
			'lp_blocks',
			'lp_categories',
			'lp_comments',
			'lp_pages',
			'lp_params',
			'lp_tags',
			'lp_titles'
		];

		db_extend();

		foreach ($tables as $table)
			$smcFunc['db_optimize_table']('{db_prefix}' . $table);
	}
}

<?php declare(strict_types=1);

/**
 * Ajax.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Utils;

use Bugo\LightPortal\Helper;
use JetBrains\PhpStorm\NoReturn;

final class Ajax
{
	use Helper;

	#[NoReturn] public function process(): void
	{
		header('content-type: application/json; charset=UTF-8');

		if (empty($this->context['user']['is_logged'])) {
			$this->sendStatus(403);

			exit(json_encode([
				'error' => 'No access'
			]));
		}

		$result = [];

		$data = $this->request()->json();

		if ($this->request()->has('topic_by_subject')) {
			$result = $this->getTopicsBySubject($data['search']);
		}

		// Do you want to add your own result?
		$this->hook('ajax', [&$result, $data]);

		exit(json_encode($result));
	}

	private function getTopicsBySubject(?string $subject = ''): array
	{
		if (empty($subject))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_poll = {int:id_poll}
				AND t.approved = {int:is_approved}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND t.id_board != {int:recycle_board}
				AND INSTR(LOWER(m.subject), {string:subject}) > 0
			ORDER BY m.subject
			LIMIT 100',
			[
				'id_poll'           => 0,
				'is_approved'       => 1,
				'id_redirect_topic' => 0,
				'recycle_board'     => empty($this->modSettings['recycle_board']) ? $this->modSettings['recycle_board'] : 0,
				'subject'           => $this->smcFunc['strtolower']($subject),
			]
		);

		$topics = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['subject']);

			$topics[] = [
				'id'      => $row['id_topic'],
				'subject' => $row['subject'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $topics;
	}
}

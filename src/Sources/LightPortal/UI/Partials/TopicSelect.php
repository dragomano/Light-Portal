<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;

if (! defined('SMF'))
	die('No direct access...');

final class TopicSelect extends AbstractSelect
{
	protected string $template = 'topic_select';

	public function getData(): array
	{
		return $this->getSelectedTopics($this->normalizeValue($this->params['value']));
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'lp_frontpage_topics',
			'multiple' => true,
			'wide'     => true,
			'more'     => true,
			'hint'     => Lang::$txt['lp_frontpage_topics_select'],
			'empty'    => Lang::$txt['lp_frontpage_topics_no_items'],
			'value'    => $this->normalizeValue(Config::$modSettings['lp_frontpage_topics'] ?? ''),
		];
	}

	private function getSelectedTopics(array $topics): array
	{
		if (empty($topics)) {
			return [];
		}

		$result = Db::$db->query('
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_topic IN ({array_int:topics})
				AND t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND {query_wanna_see_board}',
			[
				'topics'            => $topics,
				'is_approved'       => 1,
				'id_poll'           => 0,
				'id_redirect_topic' => 0,
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['subject']);

			$topics[] = [
				'label' => $row['subject'],
				'value' => $row['id_topic'],
			];
		}

		Db::$db->free_result($result);

		return $topics;
	}
}

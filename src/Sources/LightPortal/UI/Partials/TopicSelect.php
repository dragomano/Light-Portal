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
use Bugo\Compat\Lang;
use Bugo\LightPortal\Utils\ForumPermissions;

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

		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->columns(['id_topic'])
			->join(['m' => 'messages'], 'm.id_msg = t.id_first_msg', ['subject'])
			->where([
				't.id_topic'          => $topics,
				't.approved'          => 1,
				't.id_poll'           => 0,
				't.id_redirect_topic' => 0,
			]);

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard());
		}

		$result = $this->sql->execute($select);

		$topics = [];
		foreach ($result as $row) {
			Lang::censorText($row['subject']);

			$topics[] = [
				'label' => $row['subject'],
				'value' => $row['id_topic'],
			];
		}

		return $topics;
	}
}

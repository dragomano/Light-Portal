<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Articles\Queries;

use Laminas\Db\Sql\Select;
use LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

class ChosenTopicArticleQuery extends TopicArticleQuery
{
	public function init(array $params): void
	{
		$params['selected_boards'] = [];
		$params['selected_topics'] = Setting::get('lp_frontpage_topics', 'array', []);

		parent::init($params);
	}

	public function getRawData(): iterable
	{
		if (empty($this->params['selected_topics'])) {
			return [];
		}

		return parent::getRawData();
	}

	public function getTotalCount(): int
	{
		if (empty($this->params['selected_topics'])) {
			return 0;
		}

		return parent::getTotalCount();
	}

	protected function applyBaseConditions(Select $select): void
	{
		parent::applyBaseConditions($select);

		if (! empty($this->params['selected_topics'])) {
			$select->where(['t.id_topic' => $this->params['selected_topics']]);
		}
	}
}

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

class ChosenPageArticleQuery extends PageArticleQuery
{
	public function init(array $params): void
	{
		$params['selected_categories'] = [];
		$params['selected_pages'] = Setting::get('lp_frontpage_pages', 'array', []);

		parent::init($params);
	}

	public function getRawData(): iterable
	{
		if (empty($this->params['selected_pages'])) {
			return [];
		}

		return parent::getRawData();
	}

	public function getTotalCount(): int
	{
		if (empty($this->params['selected_pages'])) {
			return 0;
		}

		return parent::getTotalCount();
	}

	protected function applyBaseConditions(Select $select): void
	{
		parent::applyBaseConditions($select);

		if (! empty($this->params['selected_pages'])) {
			$select->where(['p.page_id' => $this->params['selected_pages']]);
		}
	}
}

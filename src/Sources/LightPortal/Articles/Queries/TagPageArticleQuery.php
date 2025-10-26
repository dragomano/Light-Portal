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

namespace LightPortal\Articles\Queries;

use Laminas\Db\Sql\Select;
use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class TagPageArticleQuery extends PageArticleQuery
{
	use HasRequest;

	public function init(array $params): void
	{
		$params['current_tag'] = (int) $this->request()->get('id');

		parent::init($params);

		$this->params['selected_categories'] = [];
	}

	protected function buildDataSelect(): Select
	{
		$select = parent::buildDataSelect();

		if (empty($this->params['current_tag'])) {
			return $select;
		}

		$select
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', []);

		return $select;
	}

	protected function buildCountSelect(): Select
	{
		$select = parent::buildCountSelect();

		if (empty($this->params['current_tag'])) {
			return $select;
		}

		$select
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', []);

		return $select;
	}

	protected function applyBaseConditions(Select $select): void
	{
		parent::applyBaseConditions($select);

		if (empty($this->params['current_tag'])) {
			return;
		}

		$select->where([
			'pt.tag_id'  => $this->params['current_tag'],
			'tag.status' => $this->params['status'],
		]);
	}
}

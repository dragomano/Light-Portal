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

namespace LightPortal\Articles\Services;

use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class CategoryPageArticleService extends PageArticleService
{
	use HasRequest;

	public function getParams(): array
	{
		$params = parent::getParams();
		$params['selected_categories'] = [(int) $this->request()->get('id')];

		return $params;
	}
}

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
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Utils\Icon;

if (! defined('SMF'))
	die('No direct access...');

final class CategorySelect extends AbstractSelect
{
	public function __construct(private readonly CategoryList $categoryList, protected array $params = [])
	{
		parent::__construct($params);
	}

	public function getData(): array
	{
		$list = ($this->categoryList)();

		$data = [];
		foreach ($list as $id => $cat) {
			$data[] = [
				'label' => Icon::parse($cat['icon']) . $cat['title'],
				'value' => $id,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'lp_frontpage_categories',
			'disabled' => count($this->getData()) < 2,
			'multiple' => true,
			'wide'     => true,
			'hint'     => Lang::$txt['lp_frontpage_categories_select'],
			'value'    => $this->normalizeValue(Config::$modSettings['lp_frontpage_categories'] ?? ''),
		];
	}
}

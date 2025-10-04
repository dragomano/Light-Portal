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
use Bugo\LightPortal\Lists\PageList;

if (! defined('SMF'))
	die('No direct access...');

final class PageSelect extends AbstractSelect
{
	public function __construct(private readonly PageList $pageList, protected array $params = [])
	{
		parent::__construct($params);
	}

	public function getData(): array
	{
		$list = ($this->pageList)();

		$data = [];
		foreach ($list as $id => $page) {
			$data[] = [
				'label' => $page['title'],
				'value' => $id,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'lp_frontpage_pages',
			'multiple' => true,
			'wide'     => true,
			'more'     => true,
			'hint'     => Lang::$txt['lp_frontpage_pages_select'],
			'empty'    => Lang::$txt['lp_frontpage_pages_no_items'],
			'value'    => $this->normalizeValue(Config::$modSettings['lp_frontpage_pages'] ?? ''),
		];
	}
}

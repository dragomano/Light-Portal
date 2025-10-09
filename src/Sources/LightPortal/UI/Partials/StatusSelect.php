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

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class StatusSelect extends AbstractSelect
{
	public function getData(): array
	{
		$data = [];
		foreach (Lang::$txt['lp_page_status_set'] as $value => $label) {
			$data[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'status',
			'multiple' => false,
			'search'   => false,
			'wide'     => false,
			'hint'     => '',
			'value'    => Utils::$context['lp_page']['status'] ?? 1,
		];
	}
}

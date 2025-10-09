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

final class PermissionSelect extends AbstractSelect
{
	public function getData(): array
	{
		$data = [];
		foreach (Lang::$txt['lp_permissions'] as $level => $title) {
			if (empty(Utils::$context['user']['is_admin']) && empty($level)) {
				continue;
			}

			$data[] = [
				'label' => $title,
				'value' => $level,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		$entity = $this->params['type'] ?? 'page';

		return [
			'id'       => 'permissions',
			'multiple' => false,
			'search'   => false,
			'wide'     => false,
			'hint'     => '',
			'value'    => Utils::$context['lp_' . $entity]['permissions'] ?? 0,
		];
	}
}

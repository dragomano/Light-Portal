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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;

if (! defined('SMF'))
	die('No direct access...');

final class EntryTypeSelect extends AbstractSelect
{
	public function getData(): array
	{
		$pageTypes = Utils::$context['lp_page_types'] ?? [];

		$data = [];
		foreach ($pageTypes as $value => $label) {
			if (Utils::$context['user']['is_admin'] === false && $value === EntryType::INTERNAL->name()) {
				continue;
			}

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
			'id'       => 'entry_type',
			'multiple' => false,
			'search'   => false,
			'wide'     => false,
			'hint'     => '',
			'value'    => Utils::$context['lp_page']['entry_type'] ?? EntryType::DEFAULT->name(),
		];
	}
}

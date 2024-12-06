<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;

use function func_get_args;
use function json_encode;

final class EntryTypeSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'entry_type';
		$params['data'] ??= Utils::$context['lp_page_types'] ?? [];
		$params['value'] ??= Utils::$context['lp_page']['entry_type'] ?? EntryType::DEFAULT->name();

		$data = [];
		foreach ($params['data'] as $value => $label) {
			if (Utils::$context['user']['is_admin'] === false && $value === 'internal')
				continue;

			$data[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",
				hideClearButton: true,' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				markSearchResults: true,
				options: ' . json_encode($data) . ',
				selectedValue: "' . $params['value'] . '"
			});
		</script>';
	}
}

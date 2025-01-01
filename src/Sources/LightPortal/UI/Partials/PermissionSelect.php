<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

use function func_get_args;
use function json_encode;

final class PermissionSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$entity = $params[0]['type'] ?? 'page';

		$data = [];
		foreach (Lang::$txt['lp_permissions'] as $level => $title) {
			if (empty(Utils::$context['user']['is_admin']) && empty($level))
				continue;

			$data[] = [
				'label' => $title,
				'value' => $level,
			];
		}

		return /** @lang text */ '
		<div id="permissions" name="permissions"></div>
		<script>
			VirtualSelect.init({
				ele: "#permissions",
				hideClearButton: true,' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				options: ' . json_encode($data) . ',
				selectedValue: "' . Utils::$context['lp_' . $entity]['permissions'] . '"
			});
		</script>';
	}
}

<?php declare(strict_types=1);

/**
 * PermissionSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Partials;

final class PermissionSelect extends AbstractPartial
{
	public function __invoke(string $entity = 'page'): string
	{
		$data = [];
		foreach ($this->txt['lp_permissions'] as $level => $title) {
			if (empty($this->context['user']['is_admin']) && empty($level))
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
				hideClearButton: true,' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $this->context['lp_' . $entity]['permissions'] . '"
			});
		</script>';
	}
}

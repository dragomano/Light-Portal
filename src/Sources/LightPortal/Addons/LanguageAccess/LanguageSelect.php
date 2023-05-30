<?php declare(strict_types=1);

/**
 * LanguageSelect.php
 *
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 01.05.23
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Partials\AbstractPartial;

final class LanguageSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$current_languages = $this->context['lp_block']['options']['parameters']['allowed_languages'] ?? [];
		$current_languages = is_array($current_languages) ? $current_languages : explode(',', $current_languages);

		$data = $items = [];

		foreach ($this->context['languages'] as $lang) {
			$data[] = '{label: "' . $lang['name'] . '", value: "' . $lang['filename'] . '"}';

			if (in_array($lang['filename'], $current_languages)) {
				$items[] = $this->jsEscape($lang['filename']);
			}
		}

		return /** @lang text */ '
		<div id="allowed_languages" name="allowed_languages"></div>
		<script>
			VirtualSelect.init({
				ele: "#allowed_languages",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				showValueAsTags: true,
				placeholder: "' . $this->txt['lp_language_access']['allowed_languages_subtext'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				selectAllText: "' . $this->txt['check_all'] . '",
				multiple: true,
				search: false,
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}

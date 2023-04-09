<?php declare(strict_types=1);

/**
 * ContentClassSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

final class ContentClassSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		if (empty($this->context['lp_block']['options']['no_content_class'])) {
			$data = [];
			foreach ($this->context['lp_all_content_classes'] as $key => $template) {
				$data[] = [
					'label' => sprintf($template, empty($key) ? $this->txt['no'] : $key, ''),
					'value' => $key,
				];
			}

			return /** @lang text */ '
		<div id="content_class" name="content_class"></div>
		<script>
			VirtualSelect.init({
				ele: "#content_class",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "' . $this->txt['no'] . '",
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: "' . ($this->context['lp_block']['content_class'] ?? '') . '",
				labelRenderer: function (data) {
					return `<div>${data.label}</div>`;
				}
			});
		</script>';
		}

		return '';
	}
}

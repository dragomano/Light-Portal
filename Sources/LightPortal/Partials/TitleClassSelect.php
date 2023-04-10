<?php declare(strict_types=1);

/**
 * TitleClassSelect.php
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

final class TitleClassSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		if (empty($this->context['lp_all_title_classes']))
			return '';

		$data = [];
		foreach ($this->context['lp_all_title_classes'] as $key => $template) {
			$data[] = [
				'label' => sprintf($template, empty($key) ? $this->txt['no'] : $key),
				'value' => $key,
			];
		}

		return /** @lang text */ '
		<div id="title_class" name="title_class"></div>
		<script>
			VirtualSelect.init({
				ele: "#title_class",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				showSelectedOptionsFirst: true,
				optionHeight: "60px",
				placeholder: "' . $this->txt['no'] . '",
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: "' . ($this->context['lp_block']['title_class'] ?? '') . '",
				labelRenderer: function (data) {
					return `<div>${data.label}</div>`;
				}
			});
		</script>';
	}
}

<?php declare(strict_types=1);

/**
 * CustomSelectField.php
 *
 * @package BoardNews (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */


namespace Bugo\LightPortal\Addons\BoardNews;

use Bugo\LightPortal\Areas\Fields\AbstractField;
use Bugo\LightPortal\Areas\Fields\SelectField;

if (! defined('SMF'))
	die('No direct access...');

class CustomSelectField extends SelectField
{
	public function build(): void
	{
		AbstractField::build();

		foreach ($this->options as $category) {
			$this->context['posting_fields'][$this->name]['input']['options'][$category['name']] = ['options' => []];

			foreach ($category['boards'] as $board) {
				$this->context['posting_fields']['board_id']['input']['options'][$category['name']]['options'][$board['name']] = [
					'value'    => $board['id'],
					'selected' => (bool) $board['selected'],
					'label'    => ($board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '') . ' ' . $board['name']
				];
			}
		}
	}
}

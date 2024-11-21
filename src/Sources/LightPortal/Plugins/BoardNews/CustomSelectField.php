<?php declare(strict_types=1);

/**
 * @package BoardNews (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */


namespace Bugo\LightPortal\Plugins\BoardNews;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Fields\{AbstractField, SelectField};

if (! defined('LP_NAME'))
	die('No direct access...');

class CustomSelectField extends SelectField
{
	protected function build(): void
	{
		AbstractField::build();

		foreach ($this->options as $category) {
			Utils::$context['posting_fields'][$this->name]['input']['options'][$category['name']] = ['options' => []];

			foreach ($category['boards'] as $board) {
				Utils::$context['posting_fields']['board_id']['input']['options'][$category['name']]['options'][$board['name']] = [
					'value'    => $board['id'],
					'selected' => (bool) $board['selected'],
					'label'    => ($board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '') . ' ' . $board['name']
				];
			}
		}
	}
}

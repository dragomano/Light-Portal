<?php declare(strict_types=1);

/**
 * RangeField.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Fields;

use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

class RangeField extends CustomField
{
	public function build(): void
	{
		parent::build();

		$attrs = '';

		if (isset($this->attributes['min'])) {
			$attrs .= ' min="' . $this->attributes['min'] . '"';
		}

		if (isset($this->attributes['max'])) {
			$attrs .= ' max="' . $this->attributes['max'] . '"';
		}

		if (isset($this->attributes['step'])) {
			$attrs .= ' step="' . $this->attributes['step'] . '"';
		}

		Utils::$context['posting_fields'][$this->name]['input']['html'] = '
			<div x-data="{ \'' . $this->name . '\': ' . $this->attributes['value'] . ' }">
				<input type="range" id="' . $this->name . '" name="' . $this->name . '" x-model="' . $this->name . '"' . $attrs . '>
				<span class="progress_bar amt" x-text="' . $this->name . '"></span>
			</div>';
	}
}

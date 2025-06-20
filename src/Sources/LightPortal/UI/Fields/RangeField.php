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

namespace Bugo\LightPortal\UI\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class RangeField extends CustomField
{
	protected function build(): void
	{
		parent::build();

		Utils::$context['posting_fields'][$this->name]['input']['html'] = Str::html('div', [
			'x-data' => "{ '$this->name' : {$this->attributes['value']} }",
		])
			->addHtml(
				Str::html('input', [
					'type'    => 'range',
					'id'      => $this->name,
					'name'    => $this->name,
					'x-model' => $this->name,
				])
					->setAttribute('min', $this->attributes['min'] ?? null)
					->setAttribute('max', $this->attributes['max'] ?? null)
					->setAttribute('step', $this->attributes['step'] ?? null)
			)
			->addHtml(
				Str::html('span', [
					'class'  => 'progress_bar amt',
					'style'  => 'margin-left: 10px',
					'x-text' => $this->name,
				])
			);
	}
}

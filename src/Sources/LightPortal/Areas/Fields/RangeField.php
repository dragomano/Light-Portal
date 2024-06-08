<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Fields;

use Bugo\Compat\Utils;
use Nette\Utils\Html;

if (! defined('SMF'))
	die('No direct access...');

class RangeField extends CustomField
{
	protected function build(): void
	{
		parent::build();

		Utils::$context['posting_fields'][$this->name]['input']['html'] = Html::el('div', [
			'x-data' => "{ '$this->name' : {$this->attributes['value']} }",
		])
			->addHtml(
				Html::el('input', [
					'type'    => 'range',
					'id'      => $this->name,
					'name'    => $this->name,
					'x-model' => $this->name,
				])
					->setAttribute('min', $this->attributes['min'] ?? null)
					->setAttribute('max', $this->attributes['max'] ?? null)
					->setAttribute('step', $this->attributes['step'] ?? null)
					->toHtml()
			)
			->addHtml(
				Html::el('span', [
					'class'  => 'progress_bar amt',
					'x-text' => $this->name,
				])
					->toHtml()
			)
			->toHtml();
	}
}

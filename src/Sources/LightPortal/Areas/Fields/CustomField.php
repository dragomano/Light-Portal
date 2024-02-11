<?php declare(strict_types=1);

/**
 * CustomField.php
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

class CustomField extends AbstractField
{
	public function __construct(string $name, string $label)
	{
		$this
			->setName($name)
			->setLabel($label);
	}

	public function build(): void
	{
		Utils::$context['posting_fields'][$this->name]['label']['html']  = $this->label;
		Utils::$context['posting_fields'][$this->name]['input']['html']  = $this->attributes['value'];
		Utils::$context['posting_fields'][$this->name]['input']['tab']   = $this->tab;
		Utils::$context['posting_fields'][$this->name]['input']['after'] = $this->after;
	}
}

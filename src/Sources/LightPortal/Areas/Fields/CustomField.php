<?php declare(strict_types=1);

/**
 * CustomField.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Fields;

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
		$this->context['posting_fields'][$this->name]['label']['html']  = $this->label;
		$this->context['posting_fields'][$this->name]['input']['html']  = $this->attributes['value'];
		$this->context['posting_fields'][$this->name]['input']['tab']   = $this->tab;
		$this->context['posting_fields'][$this->name]['input']['after'] = $this->after;
	}
}

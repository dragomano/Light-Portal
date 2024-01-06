<?php declare(strict_types=1);

/**
 * CheckboxField.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Fields;

if (! defined('SMF'))
	die('No direct access...');

class CheckboxField extends InputField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		$this
			->setType('checkbox')
			->setAttribute('id', $name)
			->setAttribute('class', 'checkbox');
	}

	public function setValue(mixed $value, ...$params): self
	{
		$this->setAttribute('checked', (bool) $value);

		return $this;
	}
}

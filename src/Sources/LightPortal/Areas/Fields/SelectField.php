<?php declare(strict_types=1);

/**
 * SelectField.php
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

class SelectField extends AbstractField
{
	protected array $options = [];

	public function __construct(string $name, string $label)
	{
		$this
			->setName($name)
			->setLabel($label)
			->setType('select')
			->setAttribute('id', $name);
	}

	public function setOptions(array $options): self
	{
		$this->options = $options;

		return $this;
	}

	public function build(): void
	{
		parent::build();

		foreach ($this->options as $key => $value) {
			$this->context['posting_fields'][$this->name]['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key === $this->attributes['value']
			];
		}
	}
}

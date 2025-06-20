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

use function implode;

if (! defined('SMF'))
	die('No direct access...');

class CheckboxField extends InputField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		$this->setType('checkbox');
	}

	public function setValue(mixed $value, ...$params): self
	{
		$this->setAttribute('checked', (bool) $value);

		return $this;
	}

	protected function build(): void
	{
		parent::build();

		Utils::$context['posting_fields'][$this->name]['input']['html'] = implode('', [
			Str::html('input')
				->type('checkbox')
				->id($this->name)
				->name($this->name)
				->class('checkbox')
				->checked($this->attributes['checked']),
			Str::html('label', ['class' => 'label'])
				->setAttribute('for', $this->name)
		]);

		Utils::$context['posting_fields'][$this->name]['input']['after'] = $this->description;
		Utils::$context['posting_fields'][$this->name]['input']['tab']   = $this->tab;
	}
}

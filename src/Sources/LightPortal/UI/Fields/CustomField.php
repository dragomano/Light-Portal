<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\UI\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class CustomField extends AbstractField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);
	}

	protected function build(): void
	{
		$label = $this->label;
		if ($label !== ' ') {
			$label = Str::html('label')
				->setAttribute('for', $this->name)
				->setText($label);
		}

		Utils::$context['posting_fields'][$this->name]['label']['html']  = $label;
		Utils::$context['posting_fields'][$this->name]['label']['after'] = $this->after;
		Utils::$context['posting_fields'][$this->name]['input']['html']  = $this->attributes['value'] ?? '';
		Utils::$context['posting_fields'][$this->name]['input']['after'] = $this->description;
		Utils::$context['posting_fields'][$this->name]['input']['tab']   = $this->tab;
	}
}

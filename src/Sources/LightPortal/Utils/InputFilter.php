<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

use LightPortal\Enums\VarType;
use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class InputFilter
{
	use HasRequest;

	private array $typeMap = [
		'check' => VarType::BOOLEAN,
		'float' => VarType::FLOAT,
		'int'   => VarType::INTEGER,
		'range' => VarType::INTEGER,
		'url'   => VarType::URL,
	];

	public function filter(array $configVars): array
	{
		$settings = [];

		foreach ($configVars as $var) {
			[$type, $key] = $var;

			if (! $this->request()->has($key)) {
				continue;
			}

			$value = $this->request()->get($key);

			if (isset($this->typeMap[$type])) {
				$settings[$key] = $this->typeMap[$type]->filter($value);
			} else {
				$settings[$key] = $value;
			}
		}

		return $settings;
	}
}

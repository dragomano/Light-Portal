<?php declare(strict_types=1);

/**
 * SMFTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\IntegrationHook;

if (! defined('SMF'))
	die('No direct access...');

trait SMFTrait
{
	protected function applyHook(string $name, string $method = ''): void
	{
		$name = str_replace('integrate_', '', $name);

		if (func_num_args() === 1) {
			$method = lcfirst($this->getCamelName($name));
		}

		$method = static::class . '::' . str_replace('#', '', $method);

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

		if ($name === 'init') {
			$name = 'user_info';
		}

		IntegrationHook::add('integrate_' . $name, $method . '#', false, $file);
	}
}

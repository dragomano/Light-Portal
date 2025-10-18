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

namespace LightPortal\Areas\Configs;

use Bugo\Compat\Actions\Admin\Permissions;
use Bugo\Compat\Config;
use Closure;

class VarFactory
{
	public function __construct(private readonly string $name, private readonly string $type) {}

	public function createTemplateCallback(): Closure
	{
		return fn() => function() {
			if (! function_exists('template_callback_' . $this->name)) {
				return '';
			}

			ob_start();

			call_user_func('template_callback_' . $this->name);

			return (string) ob_get_clean();
		};
	}

	public function createPermissionsCallback(): Closure
	{
		return fn() => function() {
			ob_start();

			Permissions::theme_inline_permissions($this->name);

			return ob_get_clean();
		};
	}

	public function getDefaultValue(): bool|int|string
	{
		return match ($this->type) {
			'check' => false,
			'int'   => 0,
			default => '',
		};
	}

	public function getValue(array $data = []): mixed
	{
		$value = Config::$modSettings[$this->name] ?? '';

		if ($this->type === 'select') {
			$type = gettype(array_key_last($data));

			settype($value, $type);
		}

		return $value;
	}
}

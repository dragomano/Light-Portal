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

namespace LightPortal\UI\Tables;

use LightPortal\Utils\Str;

readonly class Button
{
	public function __construct(
		private string $name,
		private string $value,
		private string $class = 'button'
	) {}

	public static function make(string $name, string $value, string $class = 'button'): string
	{
		$button = new self($name, $value, $class);

		return $button->render();
	}

	public function render(): string
	{
		return Str::html('input', [
			'type'  => 'submit',
			'name'  => $this->name,
			'value' => $this->value,
			'class' => $this->class,
		])->toHtml();
	}
}

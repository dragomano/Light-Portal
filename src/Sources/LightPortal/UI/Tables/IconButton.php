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

use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;

readonly class IconButton
{
	public function __construct(
		private string $icon,
		private array  $attributes,
		private string $class = 'button'
	) {}

	public static function make(string $icon, array $attributes, string $class = 'button'): string
	{
		$button = new self($icon, $attributes, $class);

		return $button->render();
	}

	public function render(): string
	{
		$button = Str::html('button', [
			'class' => $this->class,
		])->addHtml(Icon::get($this->icon));

		foreach ($this->attributes as $key => $value) {
			$button->setAttribute($key, $value);
		}

		return $button->toHtml();
	}
}

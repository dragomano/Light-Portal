<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Str;

class IconButton
{
	public function __construct(
		private readonly string $icon,
		private readonly array $attributes,
		private readonly string $class = 'button'
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

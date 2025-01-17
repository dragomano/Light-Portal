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

namespace Bugo\LightPortal\UI\Tables;

use Bugo\LightPortal\Utils\Str;

class Link
{
	public function __construct(
		private readonly string $text,
		private readonly array $attributes,
		private readonly string $class = 'button'
	) {}

	public static function make(string $text, array $attributes, string $class = 'button'): string
	{
		$button = new self($text, $attributes, $class);

		return $button->render();
	}

	public function render(): string
	{
		$link = Str::html('a')
			->class($this->class)
			->setText($this->text);

		foreach ($this->attributes as $key => $value) {
			$link->setAttribute($key, $value);
		}

		return $link->toHtml();
	}
}

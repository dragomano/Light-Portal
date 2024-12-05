<?php declare(strict_types=1);

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\IconColumn as BaseIconColumn;
use Bugo\Compat\Lang;

class IconColumn extends BaseIconColumn
{
	public static function make(string $name = 'icon', string $title = 'Icon'): static
	{
		return parent::make($name, Lang::$txt['custom_profile_icon'])
			->setData('icon', 'centertext');
	}
}

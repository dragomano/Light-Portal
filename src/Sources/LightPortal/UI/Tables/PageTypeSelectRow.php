<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Row;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Str;

class PageTypeSelectRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		$types = '';
		foreach (Utils::$context['lp_page_types'] as $type => $text) {
			if (Utils::$context['user']['is_admin'] === false && $type === 'internal')
				continue;

			$types .= Str::html('option', [
				'value'    => $type,
				'selected' => (new Request())->has('type') && (new Request())->get('type') === $type,
			])->setText($text);
		}

		return parent::make($value ?: Str::html('label', ['for' => 'type'])
				->setText(Lang::$txt['lp_page_type']) . ' ' .
			Str::html('select', [
				'id'       => 'type',
				'name'     => 'type',
				'onchange' => 'this.form.submit()',
			])->addHtml($types), 'floatright')
			->setPosition(RowPosition::ABOVE_COLUMN_HEADERS);
	}
}

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

namespace Bugo\LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Row;
use Bugo\Bricks\Tables\RowPosition;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Str;

use function Bugo\LightPortal\app;

class PageTypeSelectRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		$types = '';
		foreach (Utils::$context['lp_page_types'] as $type => $text) {
			if (Utils::$context['user']['is_admin'] === false && $type === EntryType::INTERNAL->name())
				continue;

			$types .= Str::html('option', [
				'value'    => $type,
				'selected' => app(Request::class)->has('type') && app(Request::class)->get('type') === $type,
			])->setText($text);
		}

		$label = Str::html('label', ['for' => 'type'])
			->setText(Lang::$txt['lp_page_type']);

		$select = Str::html('select', [
			'id'       => 'type',
			'name'     => 'type',
			'onchange' => 'this.form.submit()',
		]);

		return parent::make($value ?: $label . ' ' . $select->addHtml($types), 'floatright')
			->setPosition(RowPosition::ABOVE_COLUMN_HEADERS);
	}
}

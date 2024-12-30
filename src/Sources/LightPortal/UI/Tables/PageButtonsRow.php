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

use Bugo\Bricks\Tables\Row;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

class PageButtonsRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		return parent::make($value ?: Str::html('select', ['name' => 'page_actions'])
			->addHtml(
				Str::html('option', [
					'value' => (new Request())->has('deleted') ? 'delete_forever' : 'delete'
				])->setText(Lang::$txt[(new Request())->has('deleted') ? 'lp_action_remove_permanently' : 'remove'])
			)
			->addHtml(
				Utils::$context['allow_light_portal_approve_pages']
					? Str::html('option', ['value' => 'toggle'])
						->setText(Lang::$txt['lp_action_toggle'])
					: ''
			)
			->addHtml(
				Setting::isFrontpageMode('chosen_pages')
					? Str::html('option', ['value' => 'promote_up'])
						->setText(Lang::$txt['lp_promote_to_fp'])
					: ''
			)
			->addHtml(
				Setting::isFrontpageMode('chosen_pages')
					? Str::html('option', ['value' => 'promote_down'])
						->setText(Lang::$txt['lp_remove_from_fp'])
					: ''
			) . ' ' .
			Str::html('input', [
				'type'    => 'submit',
				'name'    => 'mass_actions',
				'value'   => Lang::$txt['quick_mod_go'],
				'class'   => 'button',
				'onclick' => "return document.forms['manage_pages']['page_actions'].value && confirm('" . Lang::$txt['quickmod_confirm'] . ");",
			])
		)
		->setClass('floatright');
	}
}

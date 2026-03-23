<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Tables;

use Bugo\Bricks\Tables\Row;
use Bugo\Compat\User;
use LightPortal\Enums\FrontPageMode;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

use function LightPortal\app;

class PageButtonsRow extends Row
{
	public static function make(string $value = '', ?string $class = null): static
	{
		$request = app(RequestInterface::class);

		$text = __($request->has('deleted') ? 'lp_action_remove_permanently' : 'remove');

		$delete = Str::html('option', [
			'value' => $request->has('deleted') ? 'delete_forever' : 'delete'
		]);

		$toggle = User::$me->allowedTo('light_portal_approve_pages')
			? Str::html('option', ['value' => 'toggle'])
				->setText(__('lp_action_toggle'))
			: '';

		$promoteUp = Setting::isFrontpageMode(FrontPageMode::CHOSEN_PAGES->value)
			? Str::html('option', ['value' => 'promote_up'])
				->setText(__('lp_promote_to_fp'))
			: '';

		$promoteDown = Setting::isFrontpageMode(FrontPageMode::CHOSEN_PAGES->value)
			? Str::html('option', ['value' => 'promote_down'])
				->setText(__('lp_remove_from_fp'))
			: '';

		$submit = Str::html('input', [
			'type'    => 'submit',
			'name'    => 'mass_actions',
			'value'   => __('quick_mod_go'),
			'class'   => 'button',
			'onclick' => "return document.forms['manage_pages']['page_actions'].value && confirm('" . __('quickmod_confirm') . ");",
		]);

		$select = Str::html('select', ['name' => 'page_actions'])
			->addHtml($delete->setText($text))
			->addHtml($toggle)
			->addHtml($promoteUp)
			->addHtml($promoteDown);

		return parent::make($value ?: $select . ' ' . $submit)->setClass('floatright');
	}
}

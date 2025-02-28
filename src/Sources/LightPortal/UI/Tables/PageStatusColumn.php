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

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Str;

class PageStatusColumn extends StatusColumn
{
	public static function make(string $name = 'status', string $title = '', int $status = 0): static
	{
		return parent::make($name, $title)
			->setData(fn($entry) => $entry['status'] >= count(Status::cases()) - 1
				? Lang::$txt['lp_page_status_set'][$entry['status']] ?? Lang::$txt['no']
				: (User::$me->allowedTo('light_portal_approve_pages')
					? Str::html('div', [
							'data-id' => $entry['id'],
							'x-data'  => '{ status: ' . ($entry['status'] === $status ? 'true' : 'false') . ' }',
							'x-init'  => '$watch(\'status\', value => entity.toggleStatus($el))',
						])->setHtml(Str::html('span', [
							':class' => '{ \'on\': status, \'off\': !status }',
							':title' => 'status ? \'' . Lang::$txt['lp_action_off'] . '\' : \'' . Lang::$txt['lp_action_on'] . '\'',
							'x-on:click.prevent' => 'status = !status',
						])
					)
					: Str::html('div', [
							'x-data' => '{ status: ' . ($entry['status'] === $status ? 'true' : 'false') . ' }',
						])->setHtml(Str::html('span', [
							':class' => '{ \'on\': status, \'off\': !status }',
							'style'  => 'cursor: inherit;',
						])
					)
				), 'centertext')
			->setSort('p.status DESC', 'p.status');
	}
}

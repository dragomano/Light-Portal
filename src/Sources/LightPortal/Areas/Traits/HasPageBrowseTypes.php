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

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\Status;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

trait HasPageBrowseTypes
{
	private string $browseType;

	private string $type;

	private int $status;

	private function calculateTypes(): void
	{
		$this->browseType = 'all';
		$this->type = '';
		$this->status = Status::ACTIVE->value;

		if ($this->userId) {
			$this->browseType = 'own';
			$this->type = ';u=' . $this->userId;
		} elseif ($this->isModerate) {
			$this->browseType = 'mod';
			$this->type = ';moderate';
		} elseif ($this->isDeleted) {
			$this->browseType = 'del';
			$this->type = ';deleted';
		}
	}

	private function changeTableTitle(): void
	{
		$titles = [
			'all' => [
				'',
				__('all'),
				Utils::$context['lp_quantities']['active_pages']
			],
			'own' => [
				';u=' . User::$me->id,
				__('lp_my_pages'),
				Utils::$context['lp_quantities']['my_pages']
			],
			'mod' => [
				';moderate',
				__('awaiting_approval'),
				Utils::$context['lp_quantities']['unapproved_pages']
			],
			'del' => [
				';deleted',
				__('lp_pages_deleted'),
				Utils::$context['lp_quantities']['deleted_pages']
			]
		];

		if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
			unset($titles['all'], $titles['mod'], $titles['del']);
		}

		Utils::$context['lp_pages']['title'] .= ': ';
		foreach ($titles as $browseType => $details) {
			if ($this->browseType === $browseType) {
				Utils::$context['lp_pages']['title'] .= Str::html('img')
					->src(Theme::$current->settings['images_url'] . '/selected.png')
					->alt('&gt;');
			}

			Utils::$context['lp_pages']['title'] .= Str::html('a')
				->href(Utils::$context['form_action'] . $details[0])
				->setText($details[1] . ' (' . $details[2] . ')');

			if ($browseType !== 'del' && count($titles) > 1) {
				Utils::$context['lp_pages']['title'] .= ' | ';
			}
		}
	}
}

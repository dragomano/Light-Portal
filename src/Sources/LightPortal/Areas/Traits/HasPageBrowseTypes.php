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

	private function getBrowseTypeCount(string $browseType): int
	{
		$whereConditions = match ($browseType) {
			'own'   => $this->buildWhereConditions((int) User::$me->id, false, false),
			'mod'   => $this->buildWhereConditions(null, true, false),
			'del'   => $this->buildWhereConditions(null, false, true),
			default => $this->buildWhereConditions(null, false, false),
		};

		return $this->getRepository()->getTotalCount('', $whereConditions);
	}

	private function changeTableTitle(): void
	{
		$titles = [
			'all' => [
				'',
				__('all'),
			],
			'own' => [
				';u=' . User::$me->id,
				__('lp_my_pages'),
			],
			'mod' => [
				';moderate',
				__('awaiting_approval'),
			],
			'del' => [
				';deleted',
				__('lp_pages_deleted'),
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
				->setText($details[1] . ' (' . $this->getBrowseTypeCount($browseType) . ')');

			if ($browseType !== 'del' && count($titles) > 1) {
				Utils::$context['lp_pages']['title'] .= ' | ';
			}
		}
	}
}

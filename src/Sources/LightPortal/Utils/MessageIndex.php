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

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Actions\MessageIndex as BaseMessageIndex;

if (! defined('SMF'))
	die('No direct access...');

final class MessageIndex extends BaseMessageIndex
{
	public static function getBoardList(array $boardListOptions = []): array
	{
		$recycleBoard = Setting::get('recycle_board', 'int');

		$defaultOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => $recycleBoard === null ? null : [$recycleBoard],
		];

		if (isset($boardListOptions['included_boards'])) {
			unset($defaultOptions['excluded_boards']);
		}

		return parent::getBoardList(array_merge($defaultOptions, $boardListOptions));
	}
}

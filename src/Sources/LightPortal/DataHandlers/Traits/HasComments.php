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

namespace LightPortal\DataHandlers\Traits;

trait HasComments
{
	protected function replaceComments(array $comments = [], bool $replace = true): array
	{
		if ($comments === []) {
			return [];
		}

		return $this->insertData('lp_comments', $comments, ['id'], $replace);
	}

	protected function replaceCommentTranslations(array $translations = []): array
	{
		if ($translations === []) {
			return [];
		}

		return $this->insertData('lp_translations', $translations, ['item_id', 'type', 'lang'], true);
	}
}

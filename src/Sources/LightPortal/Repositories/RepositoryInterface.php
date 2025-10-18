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

namespace LightPortal\Repositories;

use Laminas\Db\Sql\Predicate\Expression;

if (! defined('SMF'))
	die('No direct access...');

interface RepositoryInterface
{
	public function toggleStatus(array $items = []): void;

	public function getTranslationFilter(
		string $tableAlias = 'p',
		string $idField = 'page_id',
		array $fields = ['title', 'content', 'description']
	): Expression;
}

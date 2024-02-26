<?php declare(strict_types=1);

/**
 * CategoryModel.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Actions\PageListInterface;

if (! defined('SMF'))
	die('No direct access...');

class CategoryModel extends AbstractModel
{
	public int $id;

	public string $icon;

	public string $description;

	public int $priority;

	public int $status;

	public array $titles = [];

	public function __construct(array $postData, array $currentCategory)
	{
		$this->id = $postData['category_id'] ?? $currentCategory['id'] ?? 0;

		$this->icon = $postData['icon'] ?? $currentCategory['icon'] ?? '';

		$this->description = $postData['description'] ?? $currentCategory['description'] ?? '';

		$this->priority = $currentCategory['priority'] ?? 0;

		$this->status = $currentCategory['status'] ?? PageListInterface::STATUS_ACTIVE;
	}

	protected static function getTableName(): string
	{
		return 'lp_categories';
	}
}

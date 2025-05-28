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

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Enums\Status;

class CategoryModel extends AbstractModel
{
	public int $id;

	public string $slug;

	public string $icon;

	public int $priority;

	public int $status;

	public string $title;

	public string $description;

	public function __construct(array $data)
	{
		$this->id          = $data['category_id'] ?? $data['id'] ?? 0;
		$this->slug        = $data['slug'] ?? '';
		$this->icon        = $data['icon'] ?? '';
		$this->priority    = $data['priority'] ?? 0;
		$this->status      = $data['status'] ?? Status::ACTIVE->value;
		$this->title       = $data['title'] ?? '';
		$this->description = $data['description'] ?? '';
	}
}

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

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Enums\Status;

class TagModel extends AbstractModel
{
	public int $id;

	public string $slug;

	public string $icon;

	public int $status;

	public string $title;

	public function __construct(array $data)
	{
		$this->id     = $data['tag_id'] ?? $data['id'] ?? 0;
		$this->slug   = $data['slug'] ?? '';
		$this->icon   = $data['icon'] ?? '';
		$this->status = $data['status'] ?? Status::ACTIVE->value;
		$this->title  = $data['title'] ?? '';
	}
}

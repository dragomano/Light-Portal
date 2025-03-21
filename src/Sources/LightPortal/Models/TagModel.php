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

class TagModel extends AbstractModel
{
	public int $id;

	public string $icon;

	public int $status;

	public array $titles = [];

	public function __construct(array $data)
	{
		$this->id     = $data['tag_id'] ?? $data['id'] ?? 0;
		$this->icon   = $data['icon'] ?? '';
		$this->status = $data['status'] ?? Status::ACTIVE->value;
		$this->titles = $data['titles'] ?? [];
	}
}

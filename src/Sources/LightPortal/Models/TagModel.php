<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Enums\Status;

if (! defined('SMF'))
	die('No direct access...');

class TagModel extends AbstractModel
{
	public int $id;

	public string $icon;

	public int $status;

	public array $titles = [];

	public function __construct(array $postData, array $currentTag)
	{
		$this->id = $postData['tag_id'] ?? $currentTag['id'] ?? 0;

		$this->icon = $postData['icon'] ?? $currentTag['icon'] ?? '';

		$this->status = $currentTag['status'] ?? Status::ACTIVE->value;
	}

	protected static function getTableName(): string
	{
		return 'lp_tags';
	}
}

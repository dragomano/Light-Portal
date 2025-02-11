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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Setting;

use function array_key_first;

if (! defined('SMF'))
	die('No direct access...');

class BlockModel extends AbstractModel
{
	public int $id;

	public string $icon;

	public string $type;

	public string $note;

	public string $content;

	public string $placement;

	public int $priority;

	public int $permissions;

	public int $status;

	public string $areas;

	public string $titleClass;

	public string $contentClass;

	public array $titles = [];

	public array $options = [];

	public function __construct(array $postData, array $currentBlock)
	{
		$this->id = $postData['block_id'] ?? $currentBlock['id'] ?? 0;

		$this->icon = $postData['icon'] ?? $currentBlock['icon'] ?? '';

		$this->type = $postData['type'] ?? $currentBlock['type'] ?? '';

		$this->note = $postData['note'] ?? $currentBlock['note'] ?? '';

		$this->content = $postData['content'] ?? $currentBlock['content'] ?? '';

		$this->placement = $postData['placement'] ?? $currentBlock['placement'] ?? Placement::TOP->name();

		$this->priority = $postData['priority'] ?? $currentBlock['priority'] ?? 0;

		$this->permissions = $postData['permissions']
			?? $currentBlock['permissions']
			?? Setting::get('lp_permissions_default', 'int', Permission::MEMBER->value);

		$this->status = $currentBlock['status'] ?? Status::ACTIVE->value;

		$this->areas = $postData['areas'] ?? $currentBlock['areas'] ?? 'all';

		$this->titleClass = $postData['title_class'] ?? $currentBlock['title_class']
			?? array_key_first(Utils::$context['lp_all_title_classes']);

		$this->contentClass = $postData['content_class'] ?? $currentBlock['content_class']
			?? array_key_first(Utils::$context['lp_all_content_classes']);
	}

	protected static function getTableName(): string
	{
		return 'lp_blocks';
	}
}

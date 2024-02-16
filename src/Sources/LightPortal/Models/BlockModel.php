<?php declare(strict_types=1);

/**
 * BlockModel.php
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

use Bugo\Compat\{Config, Utils};
use Bugo\LightPortal\Actions\BlockInterface;

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

	public array $options = [];

	public array $titles = [];

	public function __construct(array $postData, array $currentBlock)
	{
		$this->id = $postData['block_id'] ?? $currentBlock['id'] ?? 0;

		$this->icon = empty($postData['block_id'])
			? ($postData['icon'] ?? $currentBlock['icon'] ?? '')
			: ($postData['icon'] ?? '');

		$this->type = $postData['type'] ?? $currentBlock['type'] ?? '';

		$this->note = $postData['note'] ?? $currentBlock['note'] ?? '';

		$this->content = $postData['content'] ?? $currentBlock['content'] ?? '';

		$this->placement = $postData['placement'] ?? $currentBlock['placement'] ?? 'top';

		$this->priority = $postData['priority'] ?? $currentBlock['priority'] ?? 0;

		$this->permissions = $postData['permissions']
			?? $currentBlock['permissions']
			?? (int) (Config::$modSettings['lp_permissions_default'] ?? 2);

		$this->status = $currentBlock['status'] ?? BlockInterface::STATUS_ACTIVE;

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

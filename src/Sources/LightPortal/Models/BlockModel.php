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

use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

class BlockModel extends AbstractModel
{
	public int $id;

	public string $icon;

	public string $type;

	public string $placement;

	public int $priority;

	public int $permissions;

	public int $status;

	public string $areas;

	public string $titleClass;

	public string $contentClass;

	public string $title;

	public string $description;

	public string $content;

	public array $options = [];

	public function __construct(array $data)
	{
		$permissions = Setting::get('lp_permissions_default', 'int', Permission::MEMBER->value);

		$this->id           = $data['block_id'] ?? $data['id'] ?? 0;
		$this->icon         = $data['icon'] ?? '';
		$this->type         = $data['type'] ?? '';
		$this->placement    = $data['placement'] ?? Placement::TOP->name();
		$this->priority     = $data['priority'] ?? 0;
		$this->permissions  = $data['permissions'] ?? $permissions;
		$this->status       = $data['status'] ?? Status::ACTIVE->value;
		$this->areas        = $data['areas'] ?? 'all';
		$this->titleClass   = $data['title_class'] ?? TitleClass::first();
		$this->contentClass = $data['content_class'] ?? ContentClass::first();
		$this->title        = $data['title'] ?? '';
		$this->content      = $data['content'] ?? '';
		$this->description  = $data['description'] ?? '';
		$this->options      = $data['options'] ?? [];

		$this->contentClass = empty($this->options['no_content_class']) ? $this->contentClass : '';
	}
}

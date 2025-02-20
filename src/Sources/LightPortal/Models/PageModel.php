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

use Bugo\Compat\User;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Setting;

use function time;

class PageModel extends AbstractModel
{
	public int $id;

	public int $categoryId;

	public int $authorId;

	public string $slug;

	public string $description;

	public string $content;

	public string $type;

	public string $entryType;

	public int $permissions;

	public int $status;

	public int $numViews;

	public int $createdAt;

	public int $updatedAt;

	public int $deletedAt;

	public int $lastCommentId;

	public array $titles = [];

	public array $tags = [];

	public array $options = [];

	public function __construct(array $data)
	{
		$permissions = Setting::get('lp_permissions_default', 'int', Permission::MEMBER->value);
		$status = User::$me->allowedTo('light_portal_approve_pages')
			? Status::ACTIVE->value
			: Status::UNAPPROVED->value;

		$this->id            = $data['page_id'] ?? $data['id'] ?? 0;
		$this->categoryId    = $data['category_id'] ?? 0;
		$this->authorId      = $data['author_id'] ?? User::$me->id;
		$this->slug          = $data['slug'] ?? '';
		$this->description   = $data['description'] ?? '';
		$this->content       = $data['content'] ?? '';
		$this->type          = $data['type'] ?? ContentType::BBC->name();
		$this->entryType     = $data['entry_type'] ?? EntryType::DEFAULT->name();
		$this->permissions   = $data['permissions'] ?? $permissions;
        $this->status        = $data['status'] ?? $status;
        $this->numViews      = $data['num_views'] ?? 0;
        $this->createdAt     = $data['created_at'] ?? time();
        $this->updatedAt     = $data['updated_at'] ?? 0;
        $this->deletedAt     = $data['deleted_at'] ?? 0;
        $this->lastCommentId = $data['last_comment_id'] ?? 0;
        $this->titles        = $data['titles'] ?? [];
		$this->tags          = $data['tags'] ?? [];
        $this->options       = $data['options'] ?? [];
	}
}

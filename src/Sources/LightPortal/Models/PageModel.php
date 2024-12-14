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

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Setting;

use function time;

if (! defined('SMF'))
	die('No direct access...');

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

	public function __construct(array $postData, array $currentPage)
	{
		$this->id = $postData['page_id'] ?? $currentPage['id'] ?? 0;

		$this->categoryId = $postData['category_id'] ?? $currentPage['category_id'] ?? 0;

		$this->authorId = $currentPage['author_id'] ?? User::$info['id'];

		$this->slug = $postData['slug'] ?? $currentPage['slug'] ?? '';

		$this->description = $postData['description'] ?? $currentPage['description'] ?? '';

		$this->content = $postData['content'] ?? $currentPage['content'] ?? '';

		$this->type = $postData['type'] ?? $currentPage['type'] ?? 'bbc';

		$this->entryType = $postData['entry_type'] ?? $currentPage['entry_type'] ?? 'default';

		$this->permissions = $postData['permissions'] ?? $currentPage['permissions']
			?? Setting::get('lp_permissions_default', 'int', 2);

		$this->status = $postData['status'] ?? $currentPage['status']
			?? (
				Utils::$context['allow_light_portal_approve_pages']
					? Status::ACTIVE->value
					: Status::UNAPPROVED->value
			);

		$this->createdAt = $currentPage['created_at'] ?? time();

		$this->updatedAt = $currentPage['updated_at'] ?? 0;

		$this->deletedAt = $currentPage['deleted_at'] ?? 0;

		$this->lastCommentId = $currentPage['last_comment_id'] ?? 0;

		$this->tags = $postData['tags'] ?? $currentPage['tags'] ?? [];
	}

	protected static function getTableName(): string
	{
		return 'lp_pages';
	}
}

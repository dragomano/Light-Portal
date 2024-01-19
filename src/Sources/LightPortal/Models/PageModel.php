<?php declare(strict_types=1);

/**
 * PageModel.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Models;

use Bugo\LightPortal\Utils\{Config, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

class PageModel extends AbstractModel
{
	public int $id;

	public int $categoryId;

	public int $authorId;

	public string $alias;

	public string $description;

	public string $content;

	public string $type;

	public int $permissions;

	public int $status;

	public int $numViews;

	public int $createdAt;

	public int $updatedAt;

	public int $deletedAt;

	public int $lastCommentId;

	public array $titles = [];

	public array $keywords = [];

	public array $options = [];

	public function __construct(array $postData, array $currentPage)
	{
		$this->id = $postData['page_id'] ?? $currentPage['id'] ?? 0;

		$this->categoryId = $postData['category_id'] ?? $currentPage['category_id'] ?? 0;

		$this->authorId = $currentPage['author_id'] ?? User::$info['id'];

		$this->alias = $postData['alias'] ?? $currentPage['alias'] ?? '';

		$this->description = $postData['description'] ?? $currentPage['description'] ?? '';

		$this->content = $postData['content'] ?? $currentPage['content'] ?? '';

		$this->type = $postData['type'] ?? $currentPage['type'] ?? 'bbc';

		$this->permissions = $postData['permissions'] ?? $currentPage['permissions'] ?? (int) (Config::$modSettings['lp_permissions_default'] ?? 2);

		$this->status = $postData['status'] ?? $currentPage['status'] ?? (int) (Utils::$context['allow_light_portal_approve_pages'] || Utils::$context['allow_light_portal_manage_pages_any']);

		$this->createdAt = $currentPage['created_at'] ?? time();
	}

	protected static function getTableName(): string
	{
		return 'lp_pages';
	}
}

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

namespace LightPortal\Models;

use Bugo\Compat\User;

if (! defined('SMF'))
	die('No direct access...');

class CommentModel extends AbstractModel
{
	public int $id;

	public int $pageId;

	public int $parentId;

	public int $authorId;

	public string $message;

	public int $createdAt;

	public int $updatedAt;

	public array $params = [];

	public function __construct(array $data)
	{
		$this->id        = $data['id'] ?? 0;
		$this->pageId    = $data['page_id'] ?? 0;
		$this->parentId  = $data['parent_id'] ?? 0;
		$this->authorId  = $data['author_id'] ?? User::$me->id;
		$this->message   = $data['message'] ?? '';
		$this->createdAt = $data['created_at'] ?? time();
		$this->updatedAt = $data['updated_at'] ?? 0;
		$this->params    = $data['params'] ?? [];
	}
}

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

if (! defined('SMF'))
	die('No direct access...');

class PageModel extends AbstractModel
{
	protected array $fields = [
		'id'              => 0,
		'category_id'     => 0,
		'author_id'       => 0,
		'slug'            => '',
		'type'            => '',
		'entry_type'      => '',
		'permissions'     => 0,
		'status'          => 0,
		'num_views'       => 0,
		'created_at'      => 0,
		'updated_at'      => 0,
		'deleted_at'      => 0,
		'last_comment_id' => 0,
	];

	protected array $extraFields = [
		'title'       => '',
		'content'     => '',
		'description' => '',
		'date'        => '',
		'time'        => '',
		'tags'        => [],
		'options'     => [],
	];

	protected array $aliases = [
		'page_id' => 'id',
	];
}

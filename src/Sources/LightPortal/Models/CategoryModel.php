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

class CategoryModel extends AbstractModel
{
	protected array $fields = [
		'id'       => 0,
		'slug'     => '',
		'icon'     => '',
		'priority' => 0,
		'status'   => 0,
	];

	protected array $extraFields = [
		'title'       => '',
		'description' => '',
	];

	protected array $aliases = [
		'category_id' => 'id',
	];
}

<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Models;

if (! defined('SMF'))
	die('No direct access...');

class BlockModel extends AbstractModel
{
	protected array $fields = [
		'id'            => 0,
		'icon'          => '',
		'type'          => '',
		'placement'     => '',
		'priority'      => 0,
		'permissions'   => 0,
		'status'        => 0,
		'areas'         => '',
		'title_class'   => '',
		'content_class' => '',
	];

	protected array $extraFields = [
		'title'       => '',
		'content'     => '',
		'description' => '',
		'options'     => [],
	];

	protected array $aliases = [
		'block_id' => 'id',
	];
}

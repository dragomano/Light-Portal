<?php declare(strict_types=1);

/**
 * IconList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class IconList
{
	use Helper;

	private string $prefix = 'fas fa-';

	public function getAll(): array
	{
		$set = [
			'home'        => 'home',
			'cog_spin'    => 'cog fa-spin',
			'pager'       => 'pager',
			'sections'    => 'folder',
			'panels'      => 'columns',
			'tools'       => 'tools',
			'info'        => 'info-circle',
			'arrows'      => 'arrows-alt',
			'content'     => 'newspaper fa-2x',
			'spider'      => 'spider',
			'access'      => 'key',
			'design'      => 'object-group',
			'main'        => 'tasks',
			'sort'        => 'sort fa-lg',
			'plus'        => 'plus',
			'export'      => 'file-export',
			'import'      => 'file-import',
			'simple'      => 'bars fa-2x',
			'tile'        => 'border-all fa-2x',
			'preview'     => 'check-double',
			'reply'       => 'reply',
			'edit'        => 'edit',
			'undo'        => 'undo',
			'remove'      => 'minus-circle',
			'close'       => 'times',
			'submit'      => 'paper-plane',
			'bold'        => 'bold',
			'italic'      => 'italic',
			'image'       => 'image',
			'link'        => 'link',
			'code'        => 'code',
			'quote'       => 'quote-right',
			'category'    => 'list-alt',
			'date'        => 'clock',
			'user'        => 'user',
			'views'       => 'eye',
			'replies'     => 'comment',
			'tag'         => 'tag',
			'users'       => 'users',
			'copyright'   => 'copyright',
			'redirect'    => 'directions',
			'calendar'    => 'calendar',
			'map_signs'   => 'map-signs',
			'arrow_right' => 'arrow-right',
			'arrow_left'  => 'arrow-left',
			'donate'      => 'donate fa-3x',
			'download'    => 'download fa-3x',
			'search'      => 'search',
			'toggle'      => '3x fa-toggle-',
			'gear'        => '2x fa-cog'
		];

		$set = array_map(fn($icon): string => $this->prefix . $icon, $set);

		$set['youtube']   = 'fab fa-youtube';
		$set['save_exit'] = 'far fa-check-square';
		$set['save']      = 'far fa-check-circle';
		$set['undo']      = 'far fa-times-circle';

		// Plugin authors can use other icon packs
		$this->hook('changeIconSet', [&$set]);

		return array_map(fn($icon): string => $this->getIcon($icon), $set);
	}
}

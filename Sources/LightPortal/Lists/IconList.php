<?php

declare(strict_types = 1);

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

final class IconList implements IconListInterface
{
	use Helper;

	public function getAll(): array
	{
		$set = [
			'basic'       => 'fas fa-cog fa-spin',
			'extra'       => 'fas fa-pager',
			'folder'      => 'fas fa-folder',
			'panels'      => 'fas fa-columns',
			'tools'       => 'fas fa-tools',
			'info'        => 'fas fa-info-circle',
			'arrows'      => 'fas fa-arrows-alt',
			'content'     => 'far fa-newspaper fa-2x',
			'spider'      => 'fas fa-spider',
			'access'      => 'fas fa-key',
			'design'      => 'fas fa-object-group',
			'main'        => 'fas fa-tasks',
			'sort'        => 'fas fa-sort fa-lg',
			'plus'        => 'fas fa-plus',
			'export'      => 'fas fa-file-export',
			'import'      => 'fas fa-file-import',
			'simple_list' => 'fas fa-bars fa-2x',
			'block_list'  => 'fas fa-border-all fa-2x',
			'save_exit'   => 'fas fa-door-open',
			'save'        => 'fas fa-save',
			'preview'     => 'far fa-check-square',
			'reply'       => 'fas fa-reply',
			'edit'        => 'fas fa-edit',
			'undo'        => 'fas fa-undo',
			'remove'      => 'fas fa-minus-circle',
			'close'       => 'fas fa-times',
			'submit'      => 'fas fa-paper-plane',
			'bold'        => 'fas fa-bold',
			'italic'      => 'fas fa-italic',
			'youtube'     => 'fab fa-youtube',
			'image'       => 'fas fa-image',
			'link'        => 'fas fa-link',
			'code'        => 'fas fa-code',
			'quote'       => 'fas fa-quote-right',
			'category'    => 'far fa-list-alt',
			'date'        => 'fas fa-clock',
			'user'        => 'fas fa-user',
			'views'       => 'fas fa-eye',
			'replies'     => 'fas fa-comment',
			'tag'         => 'fas fa-tag',
			'users'       => 'fas fa-users',
			'copyright'   => 'far fa-copyright',
			'redirect'    => 'fas fa-directions',
			'calendar'    => 'fas fa-calendar',
			'map_signs'   => 'fas fa-map-signs',
			'arrow_right' => 'fas fa-arrow-right',
			'arrow_left'  => 'fas fa-arrow-left',
			'donate'      => 'fas fa-donate fa-3x',
			'download'    => 'fas fa-download fa-3x',
			'search'      => 'fas fa-search',
		];

		// Plugin authors can use other icon packs
		$this->hook('changeIconSet', [&$set]);

		return array_map(fn($icon): string => $this->getIcon($icon), $set);
	}
}

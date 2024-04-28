<?php declare(strict_types=1);

/**
 * IconList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Lists;

use Bugo\FontAwesomeHelper\Collection;
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class IconList implements ListInterface
{
	use Helper;

	private string $prefix = 'fa-solid fa-';

	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		$set = [
			'access'        => 'universal-access',
			'arrow_left'    => 'arrow-left-long',
			'arrow_right'   => 'arrow-right-long',
			'arrows'        => 'up-down-left-right',
			'bold'          => 'bold',
			'calendar'      => 'calendar',
			'category'      => 'rectangle-list',
			'chevron_right' => 'circle-chevron-right',
			'circle_dot'    => 'circle-dot',
			'circle'        => 'circle',
			'close'         => 'xmark',
			'code'          => 'code',
			'cog_spin'      => 'cog fa-spin',
			'comments'      => 'comments fa-fw',
			'content'       => 'newspaper fa-2x',
			'copyright'     => 'copyright',
			'date'          => 'clock',
			'design'        => 'object-group',
			'dislike'       => 'arrow-down',
			'donate'        => 'circle-dollar-to-slot fa-3x',
			'download'      => 'download fa-3x',
			'edit'          => 'pen-to-square',
			'ellipsis'      => 'ellipsis',
			'export'        => 'file-export',
			'gear'          => '2x fa-gear',
			'home'          => 'house',
			'image'         => 'image',
			'import'        => 'file-import',
			'italic'        => 'italic',
			'like'          => 'arrow-up',
			'link'          => 'link',
			'list'          => 'list-ul',
			'main'          => 'table-list',
			'map_signs'     => 'signs-post',
			'pager'         => 'pager',
			'panels'        => 'table-columns',
			'plus_circle'   => 'circle-plus',
			'plus'          => 'plus fa-beat',
			'preview'       => 'eye',
			'quote'         => 'quote-right',
			'redirect'      => 'diamond-turn-right',
			'remove'        => 'trash',
			'replies'       => 'comment',
			'reply'         => 'reply',
			'search'        => 'magnifying-glass',
			'sections'      => 'folder',
			'sign_in_alt'   => 'right-to-bracket',
			'sign_out_alt'  => 'right-from-bracket',
			'simple'        => 'table-list fa-2x',
			'sort'          => 'sort',
			'spider'        => 'spider',
			'submit'        => 'paper-plane',
			'tag'           => 'tag',
			'tags'          => 'tags fa-fw',
			'task'          => 'list-check',
			'tile'          => 'border-all fa-2x',
			'tools'         => 'sliders',
			'undo'          => 'rotate-left',
			'unlike'        => 'heart-crack',
			'user_plus'     => 'user-plus',
			'user'          => 'user',
			'users'         => 'users',
			'views'         => 'eye',
		];

		$set = array_map(fn($icon): string => $this->prefix . $icon, $set);

		$set['youtube']   = 'fa-brands fa-youtube';
		$set['save_exit'] = 'fa-solid fa-envelope-open-text';
		$set['save']      = 'fa-regular fa-floppy-disk';
		$set['big_image'] = 'fa-regular fa-image fa-5x';

		// Plugin authors can extend the icon set
		$this->hook('changeIconSet', [&$set]);

		return array_map(fn($icon): string => $this->getIcon($icon), $set);
	}

	public function getList(): array
	{
		return (new Collection(['deprecated_class' => true]))->getAll();
	}
}

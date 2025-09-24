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

namespace Bugo\LightPortal\Lists;

use Bugo\FontAwesome\Icon as FontAwesome;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Icon;

if (! defined('SMF'))
	die('No direct access...');

final class IconList implements ListInterface
{
	use HasEvents;

	private string $prefix = 'fa-solid fa-';

	private array $set = [
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
		'comments'      => 'comments',
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
		'gear'          => 'gear fa-2x',
		'home'          => 'house',
		'image'         => 'image',
		'import'        => 'file-import',
		'italic'        => 'italic',
		'like'          => 'arrow-up',
		'link'          => 'link',
		'list'          => 'list-ul',
		'main'          => 'table-list',
		'map_signs'     => 'signs-post',
		'meteor'        => 'meteor',
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
		'simple'        => 'bars fa-2x',
		'sort'          => 'sort',
		'spider'        => 'spider',
		'submit'        => 'paper-plane',
		'tag'           => 'tag',
		'tags'          => 'tags',
		'task'          => 'list-check',
		'tile'          => 'columns fa-2x',
		'tools'         => 'sliders',
		'undo'          => 'rotate-left',
		'unlike'        => 'heart-crack',
		'user_plus'     => 'user-plus',
		'user'          => 'user',
		'users'         => 'users',
		'views'         => 'eye',
	];

	public function __invoke(): array
	{
		$set = array_map(fn($icon): string => $this->prefix . $icon, $this->set);

		$set['youtube']   = 'fa-brands fa-youtube';
		$set['save_exit'] = 'fa-solid fa-envelope-open-text';
		$set['save']      = 'fa-regular fa-floppy-disk';
		$set['big_image'] = 'fa-regular fa-image fa-5x';

		// Plugin authors can extend the icon set
		$this->events()->dispatch(PortalHook::changeIconSet, ['set' => &$set]);

		return array_map(static fn($icon): string => Icon::parse($icon), $set);
	}

	public function getList(): array
	{
		return FontAwesome::collection(useOldStyle: true);
	}
}

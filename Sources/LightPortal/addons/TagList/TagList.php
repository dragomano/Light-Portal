<?php

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Helpers;

/**
 * TagList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TagList
{
	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $context, $scripturl, $txt;

		if ($type !== 'tag_list')
			return;

		$tag_list = Helpers::useCache('tag_list_addon_b' . $block_id . '_u' . $context['user']['id'], 'getAllTags', '\Bugo\LightPortal\Tag');

		ob_start();

		if (!empty($tag_list)) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $scripturl, '?action=portal;sa=tags;key=', urlencode($tag['keyword']), '">', $tag['keyword'], ' (', $tag['frequency'], ')</a>';
			}
		} else
			echo $txt['lp_no_tags'];

		$content = ob_get_clean();
	}
}

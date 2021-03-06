<?php

namespace Bugo\LightPortal\Addons\Disqus;

/**
 * Disqus
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Disqus
{
	/**
	 * @var string
	 */
	public $addon_type = 'comment';

	/**
	 * Adding the new comment type
	 *
	 * Добавляем новый тип комментариев
	 *
	 * @return void
	 */
	public function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['disqus'] = 'Disqus';
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $txt;

		$config_vars[] = array('text', 'lp_disqus_addon_shortname', 'subtext' => $txt['lp_disqus_addon_shortname_subtext']);
	}

	/**
	 * Adding comment block
	 *
	 * Добавляем блок комментариев
	 *
	 * @return void
	 */
	public function comments()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'disqus' && !empty($modSettings['lp_disqus_addon_shortname'])) {
			$context['lp_disqus_comment_block'] = '
				<div id="disqus_thread" class="windowbg"></div>
				<script>
					var disqus_config = function () {
						this.page.url = "' . $context['canonical_url'] . '";
						this.page.identifier = "' . $context['lp_page']['id'] . '";
					};
					(function() {
						var d = document, s = d.createElement("script");
						s.src = "https://' . $modSettings['lp_disqus_addon_shortname'] . '.disqus.com/embed.js";
						s.setAttribute("data-timestamp", +new Date());
						(d.head || d.body).appendChild(s);
					})();
				</script>';
		}
	}
}

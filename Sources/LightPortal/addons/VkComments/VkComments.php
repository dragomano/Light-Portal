<?php

/**
 * VkComments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\VkComments;

use Bugo\LightPortal\Addons\Plugin;

class VkComments extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'comment';

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

		$txt['lp_show_comment_block_set']['vk'] = 'VKontakte';
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $modSettings, $txt;

		if (!isset($modSettings['lp_vk_comments_addon_num_comments_per_page']))
			updateSettings(array('lp_vk_comments_addon_num_comments_per_page' => 10));
		if (!isset($modSettings['lp_vk_comments_addon_allow_attachments']))
			updateSettings(array('lp_vk_comments_addon_allow_attachments' => true));

		$config_vars['vk_comments'][] = array('text', 'api_id', 'subtext' => $txt['lp_vk_comments']['api_id_subtext']);
		$config_vars['vk_comments'][] = array('int', 'num_comments_per_page');
		$config_vars['vk_comments'][] = array('check', 'allow_attachments');
		$config_vars['vk_comments'][] = array('check', 'auto_publish');
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

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'vk' && !empty($modSettings['lp_vk_comments_addon_api_id'])) {
			$num_comments      = $modSettings['lp_vk_comments_addon_num_comments_per_page'] ?? 10;
			$allow_attachments = $modSettings['lp_vk_comments_addon_allow_attachments'] ?? true;
			$auto_publish      = $modSettings['lp_vk_comments_addon_auto_publish'] ?? false;

			$context['lp_vk_comment_block'] = '
				<script src="https://vk.com/js/api/openapi.js?167"></script>
				<script>
					VK.init({
						apiId: ' . $modSettings['lp_vk_comments_addon_api_id'] . ',
						onlyWidgets: true
					});
				</script>
				<div id="vk_comments"></div>
				<script>
					VK.Widgets.Comments("vk_comments", {
						limit: ' . $num_comments . ',
						attach: ' . (!empty($allow_attachments) ? '"*"' : 'false') . ',
						autoPublish: '. (!empty($auto_publish) ? 1 : 0) . ',
						pageUrl: "' . $context['canonical_url'] . '"
					}, ' . $context['lp_page']['id'] . ');
				</script>';
		}
	}
}

<?php

namespace Bugo\LightPortal\Addons\VkComments;

/**
 * VkComments
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

class VkComments
{
	/**
	 * Allow attachments in comments (true|false)
	 *
	 * Разрешить вложения в комментариях (true|false)
	 *
	 * @var bool
	 */
	private static $allow_attachments = true;

	/**
	 * Automatically publish the comment to the user's VK page (true|false)
	 *
	 * Автоматическая публикация комментария на странице пользователя (true|false)
	 *
	 * @var bool
	 */
	private static $auto_publish = false;

	/**
	 * Adding the new comment type
	 *
	 * Добавляем новый тип комментариев
	 *
	 * @return void
	 */
	public static function init()
	{
		global $txt;

		$txt['lp_show_comment_block_set']['vk'] = 'VKontakte';
	}

	/**
	 * Добавляем настройки
	 *
	 * @param array $settings
	 * @return void
	 */
	public static function addSettings(&$settings)
	{
		global $modSettings, $txt;

		if (!isset($modSettings['lp_vk_addon_allow_attachments']))
			$modSettings['lp_vk_addon_allow_attachments'] = static::$allow_attachments;
		if (!isset($modSettings['lp_vk_addon_auto_publish']))
			$modSettings['lp_vk_addon_auto_publish'] = static::$auto_publish;

		$disabled = empty($modSettings['lp_show_comment_block']) || (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] != 'vk');

		$settings[] = array('text', 'lp_vk_addon_api_id', 'subtext' => $txt['lp_vk_addon_api_id_subtext'], 'disabled' => $disabled);
		$settings[] = array('check', 'lp_vk_addon_allow_attachments', 'disabled' => $disabled);
		$settings[] = array('check', 'lp_vk_addon_auto_publish', 'disabled' => $disabled);
	}

	/**
	 * Adding comment block
	 *
	 * Добавляем блок комментариев
	 *
	 * @return void
	 */
	public static function comments()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'vk' && !empty($modSettings['lp_vk_addon_api_id'])) {
			$num_comments      = $modSettings['lp_num_comments_per_page'] ?? 10;
			$allow_attachments = $modSettings['lp_vk_addon_allow_attachments'] ?? static::$allow_attachments;
			$auto_publish      = $modSettings['lp_vk_addon_auto_publish'] ?? static::$auto_publish;

			$context['lp_vk_comment_block'] = '
				<script src="https://vk.com/js/api/openapi.js?167"></script>
				<script>
					VK.init({
						apiId: ' . $modSettings['lp_vk_addon_api_id'] . ',
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

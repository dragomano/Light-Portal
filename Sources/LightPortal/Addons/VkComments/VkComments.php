<?php

/**
 * VkComments.php
 *
 * @package VkComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\VkComments;

use Bugo\LightPortal\Addons\Plugin;

class VkComments extends Plugin
{
	public string $type = 'comment';

	public function init()
	{
		$this->txt['lp_show_comment_block_set']['vk'] = 'VKontakte';
	}

	public function addSettings(array &$config_vars)
	{
		if (! isset($this->modSettings['lp_vk_comments_addon_num_comments_per_page']))
			updateSettings(['lp_vk_comments_addon_num_comments_per_page' => 10]);
		if (! isset($this->modSettings['lp_vk_comments_addon_allow_attachments']))
			updateSettings(['lp_vk_comments_addon_allow_attachments' => true]);

		$config_vars['vk_comments'][] = ['text', 'api_id', 'subtext' => $this->txt['lp_vk_comments']['api_id_subtext']];
		$config_vars['vk_comments'][] = ['int', 'num_comments_per_page'];
		$config_vars['vk_comments'][] = ['check', 'allow_attachments'];
		$config_vars['vk_comments'][] = ['check', 'auto_publish'];
	}

	public function comments()
	{
		if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'vk' && ! empty($this->modSettings['lp_vk_comments_addon_api_id'])) {
			$num_comments      = $this->modSettings['lp_vk_comments_addon_num_comments_per_page'] ?? 10;
			$allow_attachments = $this->modSettings['lp_vk_comments_addon_allow_attachments'] ?? true;
			$auto_publish      = $this->modSettings['lp_vk_comments_addon_auto_publish'] ?? false;

			$this->context['lp_vk_comment_block'] = '
				<script src="https://vk.com/js/api/openapi.js?167"></script>
				<script>
					VK.init({
						apiId: ' . $this->modSettings['lp_vk_comments_addon_api_id'] . ',
						onlyWidgets: true
					});
				</script>
				<div id="vk_comments"></div>
				<script>
					VK.Widgets.Comments("vk_comments", {
						limit: ' . $num_comments . ',
						attach: ' . (empty($allow_attachments) ? 'false' : '"*"') . ',
						autoPublish: '. (empty($auto_publish) ? 0 : 1) . ',
						pageUrl: "' . $this->context['canonical_url'] . '"
					}, ' . $this->context['lp_page']['id'] . ');
				</script>';
		}
	}
}

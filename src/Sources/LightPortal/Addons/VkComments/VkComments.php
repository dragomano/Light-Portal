<?php

/**
 * VkComments.php
 *
 * @package VkComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\VkComments;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class VkComments extends Plugin
{
	public string $type = 'comment';

	public function init(): void
	{
		$this->txt['lp_show_comment_block_set']['vk'] = 'VKontakte';
	}

	public function addSettings(array &$config_vars): void
	{
		$this->addDefaultValues([
			'comments_per_page' => 10,
		]);

		$config_vars['vk_comments'][] = ['text', 'api_id', 'subtext' => $this->txt['lp_vk_comments']['api_id_subtext'], 'required' => true];
		$config_vars['vk_comments'][] = ['int', 'comments_per_page'];
		$config_vars['vk_comments'][] = ['check', 'allow_attachments'];
		$config_vars['vk_comments'][] = ['check', 'auto_publish'];
	}

	public function comments(): void
	{
		if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'vk' && ! empty($this->context['lp_vk_comments_plugin']['api_id'])) {
			$num_comments      = $this->context['lp_vk_comments_plugin']['comments_per_page'] ?? 10;
			$allow_attachments = $this->context['lp_vk_comments_plugin']['allow_attachments'] ?? true;
			$auto_publish      = $this->context['lp_vk_comments_plugin']['auto_publish'] ?? false;

			$this->context['lp_vk_comment_block'] = /** @lang text */
				'
				<script src="https://vk.com/js/api/openapi.js?167"></script>
				<script>
					VK.init({
						apiId: ' . $this->context['lp_vk_comments_plugin']['api_id'] . ',
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

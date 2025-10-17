<?php declare(strict_types=1);

/**
 * @package VkComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\VkComments;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Setting;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::COMMENT)]
class VkComments extends Plugin
{
	public function init(): void
	{
		Lang::$txt['lp_comment_block_set']['vk'] = 'VKontakte';
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'comments_per_page' => 10,
		]);

		$settings = &$e->args->settings;

		$settings['vk_comments'][] = [
			'text',
			'api_id',
			'subtext' => $this->txt['api_id_subtext'],
			'required' => true
		];
		$settings['vk_comments'][] = ['int', 'comments_per_page'];
		$settings['vk_comments'][] = ['check', 'allow_attachments'];
		$settings['vk_comments'][] = ['check', 'auto_publish'];
	}

	public function comments(): void
	{
		if (Setting::getCommentBlock() !== 'vk' || empty($this->context['api_id']))
			return;

		$commentsCount    = $this->context['comments_per_page'] ?? 10;
		$allowAttachments = $this->context['allow_attachments'] ?? true;
		$autoPublish      = $this->context['auto_publish'] ?? false;

		Utils::$context['lp_vk_comment_block'] = /** @lang text */ '
			<script src="https://vk.com/js/api/openapi.js?167"></script>
			<script>
				VK.init({
					apiId: ' . $this->context['api_id'] . ',
					onlyWidgets: true
				});
			</script>
			<div id="vk_comments"></div>
			<script>
				VK.Widgets.Comments("vk_comments", {
					limit: ' . $commentsCount . ',
					attach: ' . (empty($allowAttachments) ? 'false' : '"*"') . ',
					autoPublish: '. (empty($autoPublish) ? 0 : 1) . ',
					pageUrl: "' . Utils::$context['canonical_url'] . '"
				}, ' . Utils::$context['lp_page']['id'] . ');
			</script>';
	}
}

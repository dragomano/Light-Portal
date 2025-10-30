<?php declare(strict_types=1);

/**
 * @package VkComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 29.10.25
 */

namespace LightPortal\Plugins\VkComments;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SettingsFactory;
use LightPortal\Utils\Setting;

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
		$this->addDefaultValues(['comments_per_page' => 10]);

		$e->args->settings[$this->name] = SettingsFactory::make()
			->text('api_id', [
				'subtext'  => $this->txt['api_id_subtext'],
				'required' => true,
			])
			->int('comments_per_page')
			->check('allow_attachments')
			->check('auto_publish')
			->toArray();
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

<?php declare(strict_types=1);

/**
 * @package TelegramComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2025-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 15.11.25
 */

namespace LightPortal\Plugins\TelegramComments;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SettingsFactory;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasThemes;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::COMMENT)]
class TelegramComments extends Plugin
{
	use HasThemes;

	private string $url = 'https://comments.app';

	public function init(): void
	{
		Lang::$txt['lp_comment_block_set']['telegram'] = 'Telegram';
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues(['comments_per_page' => 10]);

		$link = Str::html('a')
			->class('bbc_link')
			->target('_blank')
			->rel('noopener')
			->href($this->url)
			->addText($this->txt['site_id_subtext'])
			->toHtml();

		$e->args->settings[$this->name] = SettingsFactory::make()
			->text('site_id', [
				'subtext'  => $link,
				'required' => true,
			])
			->int('comments_per_page')
			->color('widget_color')
			->check('use_different_colors_for_names')
			->select('feedback', $this->txt['feedback_set'])
			->select('icons', $this->txt['icons_set'])
			->multiselect('dark_themes', $this->getForumThemes())
			->toArray();
	}

	public function comments(): void
	{
		if (Setting::getCommentBlock() !== 'telegram')
			return;

		if (empty($this->context['site_id']))
			return;

		Utils::$context['lp_telegram_comment_block'] = /** @lang text */ '
			<div class="telegram_comments windowbg"></div>
			<script src="https://comments.app/js/widget.js?3"
				data-comments-app-website="' . $this->context['site_id'] . '"
				data-limit="' . $this->context['comments_per_page'] . '"
				data-color="' . str_replace('#', '', $this->context['widget_color']) . '"
				' . ($this->context['use_different_colors_for_names'] === '1' ? 'data-colorful="1"' : '') . '
				' . ($this->context['feedback'] === '1' ? 'data-dislikes="1"' : '') . '
				' . ($this->context['icons'] === '1' ? 'data-outlined="1"' : '') . '
				data-dark="' . ($this->isDarkTheme($this->context['dark_themes']) ? '1' : '0') . '"
				async
			>
			</script>';
	}
}

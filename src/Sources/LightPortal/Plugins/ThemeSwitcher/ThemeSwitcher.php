<?php

/**
 * @package ThemeSwitcher (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 03.11.24
 */

namespace Bugo\LightPortal\Plugins\ThemeSwitcher;

use Bugo\Compat\Theme;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Enums\Hook;
use Nette\Utils\Html;

if (! defined('LP_NAME'))
	die('No direct access...');

class ThemeSwitcher extends Block
{
	public string $icon = 'fas fa-desktop';

	public function init(): void
	{
		$this->applyHook(Hook::manageThemes);
	}

	public function manageThemes(): void
	{
		if ($this->request()->only(['done', 'do'])) {
			$this->cache()->flush();
		}
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'theme_switcher')
			return;

		$themes = $this->getForumThemes();

		if ($themes === [])
			return;

		$id = $data->id;

		$container = Html::el('div')
			->class('themeswitcher centertext');

		$select = Html::el('select')
			->id("lp_block_{$id}_themeswitcher")
			->setAttribute('onchange', "lp_block_{$id}_themeswitcher_change();")
			->setAttribute('disabled', count($themes) < 2 ? 'disabled' : null);

		foreach ($themes as $themeId => $name) {
			$option = Html::el('option')
				->value($themeId)
				->setText($name);

			if (Theme::$current->settings['theme_id'] === $themeId) {
				$option->setAttribute('selected', 'selected');
			}

			$select->addHtml($option);
		}

		$container->addHtml($select);

		$script = Html::el('script')
			->setHtml("
			function lp_block_{$id}_themeswitcher_change() {
				let lp_block_{$id}_themeswitcher_theme_id = document.getElementById('lp_block_{$id}_themeswitcher').value;
				let search = window.location.search.split(';');
				let search_args = search.filter(function (item) {
					return !item.startsWith('theme=') && !item.startsWith('?theme=');
				});
				search = search_args.join(';');
				search = search != '' ? search + ';' : '?';
				window.location = window.location.origin + window.location.pathname + search + 'theme=' + lp_block_{$id}_themeswitcher_theme_id;
			}
		");

		$container->addHtml($script);

		echo $container;
	}
}

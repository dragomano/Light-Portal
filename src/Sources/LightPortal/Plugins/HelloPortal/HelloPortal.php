<?php declare(strict_types=1);

/**
 * @package HelloPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\HelloPortal;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

use function array_combine;
use function str_contains;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class HelloPortal extends Plugin
{
	public string $type = 'other';

	private array $themes = [false, 'dark', 'modern', 'flattener'];

	public function init(): void
	{
		$this->applyHook(Hook::menuButtons);
	}

	public function menuButtons(): void
	{
		if ($this->request()->isNot('admin') || empty($steps = $this->getStepData()))
			return;

		if (empty($this->request('area')) || empty(Utils::$context['template_layers']))
			return;

		if (str_contains((string) $this->request('area'), 'lp_')) {
			$this->setTemplate();

			Utils::$context['template_layers'][] = 'tour_info';
		}

		Lang::load('Post');

		$resources = [
			['type' => 'css', 'url' => 'https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs.min.css'],
		];

		if (! empty($this->context['theme'])) {
			$theme = $this->context['theme'] . '.css';
			$resources[] = ['type' => 'css', 'url' => 'https://cdn.jsdelivr.net/npm/intro.js@4/themes/introjs-' . $theme];
		}

		if (Utils::$context['right_to_left']) {
			$resources[] = ['type' => 'css', 'url' => 'https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs-rtl.min.css'];
		}

		$resources[] = ['type' => 'js', 'url' => 'https://cdn.jsdelivr.net/npm/intro.js@4/minified/intro.min.js'];

		$this->loadExternalResources($resources);

		Theme::addInlineJavaScript('
		function runTour() {
			introJs().setOptions({
				tooltipClass: "lp_addon_hello_portal",
				nextLabel: ' . Utils::escapeJavaScript(Lang::$txt['admin_next']) . ',
				prevLabel: ' . Utils::escapeJavaScript(Lang::$txt['back']) . ',
				doneLabel: ' . Utils::escapeJavaScript(Lang::$txt['attach_dir_ok']) . ',
				steps: [' . $steps . '],
				showProgress: ' . (
					empty($this->context['show_progress']) ? 'false' : 'true'
				) . ',
				showButtons: ' . (
					empty($this->context['show_buttons']) ? 'false' : 'true'
				) . ',
				showBullets: false,
				exitOnOverlayClick: ' . (
					empty($this->context['exit_on_overlay_click']) ? 'false' : 'true'
				) . ',
				keyboardNavigation: ' . (
					empty($this->context['keyboard_navigation']) ? 'false' : 'true'
				) . ',
				disableInteraction: ' . (
					empty($this->context['disable_interaction']) ? 'false' : 'true'
				) . ',
				scrollToElement: true,
				scrollTo: "tooltip"
			}).start();
		}');
	}

	public function addSettings(Event $e): void
	{
		$settings = &$e->args->settings;

		$settings[$this->name][] = [
			'select', 'theme', array_combine($this->themes, $this->txt['theme_set'])
		];
		$settings[$this->name][] = ['check', 'show_progress'];
		$settings[$this->name][] = ['check', 'show_buttons'];
		$settings[$this->name][] = ['check', 'exit_on_overlay_click'];
		$settings[$this->name][] = ['check', 'keyboard_navigation'];
		$settings[$this->name][] = ['check', 'disable_interaction'];
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Intro.js',
			'link' => 'https://github.com/usablica/intro.js',
			'author' => 'Afshin Mehrabani',
			'license' => [
				'name' => 'GNU AGPLv3',
				'link' => 'https://github.com/usablica/intro.js/blob/master/license.md'
			]
		];
	}

	private function getStepData(): string
	{
		$this->setTemplate('steps');

		$steps = getSteps($this->txt, Config::$modSettings);

		if ($this->isCurrentArea('lp_settings', 'basic'))
			return $steps['basic_settings'];

		if ($this->isCurrentArea('lp_settings', 'extra', false))
			return $steps['extra_settings'];

		if ($this->isCurrentArea('lp_settings', 'panels', false))
			return $steps['panels'];

		if ($this->isCurrentArea('lp_settings', 'misc', false))
			return $steps['misc'];

		if ($this->isCurrentArea('lp_blocks'))
			return $steps['blocks'];

		if ($this->isCurrentArea('lp_pages'))
			return $steps['pages'];

		if ($this->isCurrentArea('lp_categories'))
			return $steps['categories'];

		if ($this->isCurrentArea('lp_plugins'))
			return $steps['plugins'];

		if ($this->isCurrentArea('lp_plugins', 'add', false))
			return $steps['add_plugins'];

		return '';
	}

	private function isCurrentArea(string $area, string $sa = 'main', bool $canBeEmpty = true): bool
	{
		return $this->request()->has('area') && $this->request('area') === $area &&
			(
				$canBeEmpty
				? (Utils::$context['current_subaction'] === $sa || empty(Utils::$context['current_subaction']))
				: Utils::$context['current_subaction'] === $sa
			);
	}
}

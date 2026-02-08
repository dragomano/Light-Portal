<?php declare(strict_types=1);

/**
 * @package LatteLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2026 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 05.11.25
 */

namespace LightPortal\Plugins\LatteLayouts;

use Bugo\Compat\Theme;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SettingsFactory;
use LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::FRONTPAGE, showSaveButton: false)]
class LatteLayouts extends Plugin
{
	private string $extension = '.latte';

	public function addSettings(Event $e): void
	{
		$this->txt['note'] = sprintf(
			$this->txt['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$e->args->settings[$this->name] = SettingsFactory::make()
			->desc('note')
			->title('example')
			->custom('_', $this->showExamples())
			->toArray();
	}

	public function frontLayouts(Event $e): void
	{
		if (! str_contains($e->args->layout, $this->extension))
			return;

		$e->args->renderer = new LatteRenderer();
	}

	public function layoutExtensions(Event $e): void
	{
		$e->args->extensions[] = $this->extension;
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Latte',
			'link' => 'https://latte.nette.org',
			'author' => 'David Grudl',
			'license' => [
				'name' => 'the New BSD License',
				'link' => 'https://github.com/nette/latte/blob/master/license.md'
			]
		];
	}

	private function showExamples(): string
	{
		$examples = glob(__DIR__ . '/layouts/*' . $this->extension);

		$list = Str::html('ul', ['class' => 'bbc_list']);

		foreach ($examples as $file) {
			$file = basename($file);
			$list->addHtml(
				Str::html('li')->setHtml(
					Str::html('a', $file)->href(LP_ADDON_URL . '/LatteLayouts/layouts/' . $file)
				)
			);
		}

		return Str::html('div', ['class' => ContentClass::ROUNDFRAME->value])
			->setHtml($list)
			->toHtml();
	}
}

<?php declare(strict_types=1);

/**
 * @package LatteLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 10.12.24
 */

namespace Bugo\LightPortal\Plugins\LatteLayouts;

use Bugo\Compat\Theme;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Str;

use function basename;
use function glob;
use function sprintf;
use function str_contains;

if (! defined('LP_NAME'))
	die('No direct access...');

class LatteLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.latte';

	public function addSettings(Event $e): void
	{
		$this->txt['note'] = sprintf(
			$this->txt['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$e->args->settings[$this->name][] = ['desc', 'note'];
		$e->args->settings[$this->name][] = ['title', 'example'];
		$e->args->settings[$this->name][] = ['callback', '_', $this->showExamples()];
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

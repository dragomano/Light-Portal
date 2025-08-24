<?php declare(strict_types=1);

/**
 * @package Memory (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 24.08.25
 */

namespace Bugo\LightPortal\Plugins\Memory;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Games;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * https://svelte.dev/playground/9786b11205ee4bd49834e85ea288204e?version=5.25.2
 */
class Memory extends Games
{
	public string $icon = 'fas fa-memory';

	public function prepareAssets(Event $e): void
	{
		$e->args->assets['scripts'][$this->name][] = Config::$boardurl . '/Sources/LightPortal/Plugins/Memory/memory.js';
	}

	public function prepareContent(): void
	{
		$this->handleApi();

		echo /** @lang text */ '
		<div class="memory_game"></div>
		<script type="module">
			usePortalApi("' . LP_BASE_URL . ';api=' . $this->name . '", "memory/memory.js")
		</script>';
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Emoji One (v1)',
			'link' => 'https://icon-sets.iconify.design/emojione-v1',
			'author' => 'Emoji One',
			'license' => [
				'name' => 'CC BY-SA 4.0',
				'link' => 'https://creativecommons.org/licenses/by-sa/4.0/'
			]
		];
	}

	private function handleApi(): void
	{
		if ($this->request()->hasNot('api'))
			return;

		$this->response()->exit($this->preparedData());
	}

	private function preparedData(): array
	{
		return [
			'txt'     => $this->txt,
			'context' => [
				'locale'  => Lang::$txt['lang_dictionary'],
			],
		];
	}
}

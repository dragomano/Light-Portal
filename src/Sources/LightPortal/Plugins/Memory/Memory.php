<?php declare(strict_types=1);

/**
 * @package Memory (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2025-2026 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 05.10.26
 */

namespace LightPortal\Plugins\Memory;

use LightPortal\Enums\License;
use LightPortal\Plugins\CreditsFactory;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\GameBlock;
use LightPortal\Plugins\PluginAttribute;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * https://svelte.dev/playground/9786b11205ee4bd49834e85ea288204e?version=5.25.2
 */
#[PluginAttribute(icon: 'fas fa-memory')]
class Memory extends GameBlock
{
	public function prepareAssets(Event $e): void
	{
		$e->args->builder->scripts()->add('memory.js');
	}

	public function prepareContent(Event $e): void
	{
		$this->handleApiRequest($e);

		echo /** @lang text */ '
		<div class="memory_game" id="memory_game_' . $e->args->id . '"></div>
		<script type="module">
			usePortalApi("' . $this->buildApiUrl($e) . '", "memory/memory.js")
		</script>';
	}

	public function credits(Event $e): void
	{
		$e->args->links[$this->name] = CreditsFactory::make()
			->title('Emoji One (v1)')
			->link('https://icon-sets.iconify.design/emojione-v1')
			->author('Emoji One')
			->license(License::CcBySa4)
			->toArray();
	}

	protected function getApiData(Event $e): array
	{
		return ['txt' => $this->txt, 'context' => ['locale' => __('lang_dictionary')]];
	}
}

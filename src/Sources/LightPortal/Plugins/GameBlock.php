<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use LightPortal\Enums\PluginType;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: [PluginType::BLOCK, PluginType::GAMES])]
abstract class GameBlock extends Block
{
	protected function isApiRequestForThisBlock(Event $e): bool
	{
		if (! $this->request()->has('api')) {
			return false;
		}

		if ($this->request()->get('api') !== $this->name) {
			return false;
		}

		if ($this->request()->get('id') != $e->args->id) {
			return false;
		}

		return true;
	}

	protected function handleApiRequest(Event $e): void
	{
		if (! $this->isApiRequestForThisBlock($e))
			return;

		$this->response()->exit($this->getApiData($e));
	}

	protected function getApiData(Event $e): array
	{
		return [];
	}

	protected function buildApiUrl(Event $e): string
	{
		$currentUrl = $this->request()->url();

		$separator = str_contains($currentUrl, '?') ? ';' : '?';

		return $currentUrl . $separator . 'api=' . $this->name . ';id=' . $e->args->id;
	}
}

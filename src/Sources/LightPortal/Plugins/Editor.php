<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use LightPortal\Enums\PluginType;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::EDITOR)]
abstract class Editor extends Plugin
{
	abstract public function prepareEditor(Event $e): void;

	abstract protected function getSupportedContentTypes(): array;

	protected function isContentSupported(array $object): bool
	{
		$type = $object['type'] ?? null;
		$contentType = $object['options']['content'] ?? null;

		foreach ($this->getSupportedContentTypes() as $supportedType) {
			if ($type === $supportedType || $contentType === $supportedType) {
				return true;
			}
		}

		return false;
	}
}

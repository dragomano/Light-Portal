<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

interface PluginInterface
{
	public function getCamelName(): string;

	public function getSnakeName(): string;

	public function getPluginType(): string;

	public function getPluginIcon(): string;

	public function isPluginHasSaveButton(): bool;

	public function addDefaultValues(array $values): void;

	public function loadExternalResources(array $resources): void;
}

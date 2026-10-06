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

namespace LightPortal\Assets;

use Bugo\Compat\Theme;

if (! defined('LP_NAME'))
	die('No direct access...');

final readonly class ExternalCss implements ExternalResource
{
	public function __construct(
		private string $url,
		private string $id = '',
	) {}

	public function load(): void
	{
		Theme::loadCSSFile($this->url, ['external' => true], $this->id);
	}
}

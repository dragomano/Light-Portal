<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.11.25
 */

namespace LightPortal\Plugins\AdsBlock;

use Bugo\Compat\Utils;
use LightPortal\UI\TemplateLoader;

trait HasMagicThings
{
	protected function isRepliesBelowMinimum(): bool
	{
		return isset(Utils::$context['lp_ads_block_plugin']['min_replies'])
			&& Utils::$context['topicinfo']['num_replies'] < (int) Utils::$context['lp_ads_block_plugin']['min_replies'];
	}

	protected function showBlocks(string $panel = ''): void
	{
		echo TemplateLoader::fromFile('partials/_panel', [
			'panel'  => $panel,
			'blocks' => Utils::$context['lp_ads_blocks'],
		], false);
	}
}

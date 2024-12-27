<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock;

use Bugo\Compat\Utils;

trait RepliesComparisonTrait
{
	protected function isRepliesBelowMinimum(): bool
	{
		return isset(Utils::$context['lp_ads_block_plugin']['min_replies'])
			&& Utils::$context['topicinfo']['num_replies'] < (int) Utils::$context['lp_ads_block_plugin']['min_replies'];
	}
}

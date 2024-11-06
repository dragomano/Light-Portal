<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock\Traits;

use Bugo\Compat\Lang;

use function array_combine;

trait PlacementProviderTrait
{
	protected function getPlacements(): array
	{
		return array_combine(
			[
				'board_top',
				'board_bottom',
				'topic_top',
				'topic_bottom',
				'before_first_post',
				'before_every_first_post',
				'before_every_last_post',
				'before_last_post',
				'after_first_post',
				'after_every_first_post',
				'after_every_last_post',
				'after_last_post',
				'page_top',
				'page_bottom',
			],
			Lang::$txt['lp_ads_block']['placement_set']
		);
	}
}

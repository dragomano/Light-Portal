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

namespace Bugo\LightPortal\Plugins\AdsBlock\Hooks;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\AdsBlock\Traits\PlacementProviderTrait;
use Bugo\LightPortal\Utils\RequestTrait;

use function array_merge;
use function dirname;

class AdminAreas
{
	use RequestTrait;
	use PlacementProviderTrait;

	public function __invoke(): void
	{
		if ($this->request()->has('area') && $this->request('area') === 'lp_blocks') {
			require_once dirname(__DIR__) . '/template.php';

			Utils::$context['template_layers'][] = 'ads_block_form';
		}

		if (
			$this->request()->has('area')
			&& $this->request('area') === 'lp_settings'
			&& Utils::$context['current_subaction'] === 'panels'
		) {
			unset(Utils::$context['lp_block_placements']['ads']);

			Utils::$context['lp_block_placements'] = array_merge(
				Utils::$context['lp_block_placements'], $this->getPlacements()
			);
		}
	}
}

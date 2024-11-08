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
use Bugo\LightPortal\Plugins\AdsBlock\Traits\RepliesComparisonTrait;

use function dirname;

class DisplayButtons
{
	use RepliesComparisonTrait;

	public function __invoke(): void
	{
		if ($this->isTopicNumRepliesLesserThanMinReplies())
			return;

		require_once dirname(__DIR__) . '/template.php';

		Utils::$context['template_layers'][] = 'ads_placement_topic';
	}
}

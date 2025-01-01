<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock\Hooks;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\AdsBlock\Placement;
use Bugo\LightPortal\Plugins\AdsBlock\RepliesComparisonTrait;

use function lp_show_blocks;
use function ob_get_clean;
use function ob_start;

class PrepareDisplayContext
{
	use RepliesComparisonTrait;

	public function __invoke(array $output): void
	{
		if (empty(Utils::$context['lp_ads_blocks']) || ($this->isRepliesBelowMinimum()))
			return;

		$showOldestFirst = empty(Theme::$current->options['view_newest_first']);

		$counter = $output['counter'] + 1;

		$currentCounter = $showOldestFirst
			? Utils::$context['start']
			: Utils::$context['total_visible_posts'] - Utils::$context['start'];

		if (
			Utils::$context['lp_ads_blocks'][Placement::BEFORE_FIRST_POST->name()]
			&& $currentCounter == $output['counter']
			&& empty(Utils::$context['start'])
		) {
			lp_show_blocks(Placement::BEFORE_FIRST_POST->name());
		}

		if (Utils::$context['lp_ads_blocks'][Placement::BEFORE_EVERY_FIRST_POST->name()] && $currentCounter == $output['counter']) {
			lp_show_blocks(Placement::BEFORE_EVERY_FIRST_POST->name());
		}

		if (
			Utils::$context['lp_ads_blocks']['after_first_post']
			&& ($counter == ($showOldestFirst ? 2 : Utils::$context['total_visible_posts'] - 2))
		) {
			lp_show_blocks('after_first_post');
		}

		if (
			Utils::$context['lp_ads_blocks']['after_every_first_post']
			&& ($output['counter'] == ($showOldestFirst ? Utils::$context['start'] + 1 : $currentCounter - 1))
		) {
			lp_show_blocks('after_every_first_post');
		}

		$beforeEveryLastPost = $showOldestFirst
			? $counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0
			: (
				$output['id'] == Utils::$context['topic_first_message']
				|| (Utils::$context['total_visible_posts'] - $counter) % Utils::$context['messages_per_page'] == 0
			);

		if (Utils::$context['lp_ads_blocks'][Placement::BEFORE_EVERY_LAST_POST->name()] && $beforeEveryLastPost) {
			lp_show_blocks(Placement::BEFORE_EVERY_LAST_POST->name());
		}

		if (
			Utils::$context['lp_ads_blocks'][Placement::BEFORE_LAST_POST->name()]
			&& $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])
		) {
			lp_show_blocks(Placement::BEFORE_LAST_POST->name());
		}

		if (
			Utils::$context['lp_ads_blocks'][Placement::AFTER_EVERY_LAST_POST->name()]
			&& ($counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0)
		) {
			$afterEveryLastPost = function (): string {
				ob_start();
				lp_show_blocks(Placement::AFTER_EVERY_LAST_POST->name());
				return ob_get_clean();
			};

			Theme::addInlineJavaScript('
	    var quickModForm = document.querySelector("#quickModForm > div.windowbg:last-of-type");
	    if (quickModForm) {
	        quickModForm.insertAdjacentHTML("afterend", ' . Utils::escapeJavaScript($afterEveryLastPost()) . ');
	    }', true);
		}

		if (
			Utils::$context['lp_ads_blocks'][Placement::AFTER_LAST_POST->name()]
			&& $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])
		) {
			$afterLastPost = function (): string {
				ob_start();
				lp_show_blocks(Placement::AFTER_LAST_POST->name());
				return ob_get_clean();
			};

			Theme::addInlineJavaScript('
	    var quickModForm = document.getElementById("quickModForm");
	    if (quickModForm) {
	        quickModForm.insertAdjacentHTML("beforeend", ' . Utils::escapeJavaScript($afterLastPost()) . ');
	    }', true);
		}
	}
}

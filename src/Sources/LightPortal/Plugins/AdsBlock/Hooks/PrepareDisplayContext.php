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

use Bugo\Compat\{Theme, Utils};
use Bugo\LightPortal\Plugins\AdsBlock\Traits\RepliesComparisonTrait;

use function lp_show_blocks;
use function ob_get_clean;
use function ob_start;

class PrepareDisplayContext
{
	use RepliesComparisonTrait;

	public function __invoke(array $output): void
	{
		if (empty(Utils::$context['lp_ads_blocks']) || ($this->isTopicNumRepliesLesserThanMinReplies()))
			return;

		$showOldestFirst = empty(Theme::$current->options['view_newest_first']);

		$counter = $output['counter'] + 1;

		$currentCounter = $showOldestFirst
			? Utils::$context['start']
			: Utils::$context['total_visible_posts'] - Utils::$context['start'];

		/**
		 * Display ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if (
			Utils::$context['lp_ads_blocks']['before_first_post']
			&& $currentCounter == $output['counter']
			&& empty(Utils::$context['start'])
		) {
			lp_show_blocks('before_first_post');
		}

		/**
		 * Display ads before each first message on the page
		 *
		 * Вывод рекламы перед каждым первым сообщением
		 */
		if (Utils::$context['lp_ads_blocks']['before_every_first_post'] && $currentCounter == $output['counter']) {
			lp_show_blocks('before_every_first_post');
		}

		/**
		 * Display ads after the first message
		 *
		 * Вывод рекламы после первого сообщения
		 */
		if (
			Utils::$context['lp_ads_blocks']['after_first_post']
			&& ($counter == ($showOldestFirst ? 2 : Utils::$context['total_visible_posts'] - 2))
		) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Display ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if (
			Utils::$context['lp_ads_blocks']['after_every_first_post']
			&& ($output['counter'] == ($showOldestFirst ? Utils::$context['start'] + 1 : $currentCounter - 1))
		) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Display ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		$beforeEveryLastPost = $showOldestFirst
			? $counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0
			: (
				$output['id'] == Utils::$context['topic_first_message']
				|| (Utils::$context['total_visible_posts'] - $counter) % Utils::$context['messages_per_page'] == 0
			);

		if (Utils::$context['lp_ads_blocks']['before_every_last_post'] && $beforeEveryLastPost) {
			lp_show_blocks('before_every_last_post');
		}

		/**
		 * Display ads before the last message
		 *
		 * Вывод рекламы перед последним сообщением
		 */
		if (
			Utils::$context['lp_ads_blocks']['before_last_post']
			&& $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])
		) {
			lp_show_blocks('before_last_post');
		}

		/**
		 * Display ads after each last message on the page
		 *
		 * Вывод рекламы после каждого последнего сообщения
		 */
		if (
			Utils::$context['lp_ads_blocks']['after_every_last_post']
			&& ($counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0)
		) {
			ob_start();

			lp_show_blocks('after_every_last_post');

			$afterEveryLastPost = ob_get_clean();

			Theme::addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$(' . Utils::escapeJavaScript($afterEveryLastPost) . ').insertAfter("#quickModForm > div.windowbg:last");
		});', true);
		}

		/**
		 * Display ads after the last message
		 *
		 * Вывод рекламы после последнего сообщения
		 */
		if (
			Utils::$context['lp_ads_blocks']['after_last_post']
			&& $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])
		) {
			ob_start();

			lp_show_blocks('after_last_post');

			$afterLastPost = ob_get_clean();

			Theme::addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$("#quickModForm").append(' . Utils::escapeJavaScript($afterLastPost) . ');
		});', true);
		}
	}
}

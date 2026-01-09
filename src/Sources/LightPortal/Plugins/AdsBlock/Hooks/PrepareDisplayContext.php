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

namespace LightPortal\Plugins\AdsBlock\Hooks;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Plugins\AdsBlock\Placement;
use LightPortal\Plugins\AdsBlock\HasMagicThings;

class PrepareDisplayContext
{
	use HasMagicThings;

	public function __invoke(array $output): void
	{
		if (! $this->shouldProcessAds())
			return;

		$context = $this->buildContext($output);

		$this->handleBeforeFirstPost($context);
		$this->handleBeforeEveryFirstPost($context);
		$this->handleAfterFirstPost($context);
		$this->handleAfterEveryFirstPost($context);
		$this->handleBeforeEveryLastPost($context);
		$this->handleBeforeLastPost($context);
		$this->handleAfterEveryLastPost($context);
		$this->handleAfterLastPost($context);
	}

	private function shouldProcessAds(): bool
	{
		return ! empty(Utils::$context['lp_ads_blocks']) && ! $this->isRepliesBelowMinimum();
	}

	private function buildContext(array $output): array
	{
		$showOldestFirst = empty(Theme::$current->options['view_newest_first']);

		return [
			'output'            => $output,
			'show_oldest_first' => $showOldestFirst,
			'counter'           => $output['counter'] + 1,
			'current_counter'   =>  $showOldestFirst
				? Utils::$context['start']
				: Utils::$context['total_visible_posts'] - Utils::$context['start'],
		];
	}

	private function hasBlock(string $placement): bool
	{
		return ! empty(Utils::$context['lp_ads_blocks'][$placement]);
	}

	private function handleBeforeFirstPost(array $context): void
	{
		if (! $this->hasBlock(Placement::BEFORE_FIRST_POST->name()))
			return;

		if ($context['current_counter'] === $context['output']['counter'] && empty(Utils::$context['start'])) {
			$this->showBlocks(Placement::BEFORE_FIRST_POST->name());
		}
	}

	private function handleBeforeEveryFirstPost(array $context): void
	{
		if (! $this->hasBlock(Placement::BEFORE_EVERY_FIRST_POST->name()))
			return;

		if ($context['current_counter'] === $context['output']['counter']) {
			$this->showBlocks(Placement::BEFORE_EVERY_FIRST_POST->name());
		}
	}

	private function handleAfterFirstPost(array $context): void
	{
		if (! $this->hasBlock(Placement::AFTER_FIRST_POST->name()))
			return;

		$targetCounter = $context['show_oldest_first']
			? 2
			: Utils::$context['total_visible_posts'] - 2;

		if ($context['counter'] === $targetCounter) {
			$this->showBlocks(Placement::AFTER_FIRST_POST->name());
		}
	}

	private function handleAfterEveryFirstPost(array $context): void
	{
		if (! $this->hasBlock(Placement::AFTER_EVERY_FIRST_POST->name()))
			return;

		$targetCounter = $context['show_oldest_first']
			? Utils::$context['start'] + 1
			: $context['current_counter'] - 1;

		if ($context['output']['counter'] === $targetCounter) {
			$this->showBlocks(Placement::AFTER_EVERY_FIRST_POST->name());
		}
	}

	private function handleBeforeEveryLastPost(array $context): void
	{
		if (! $this->hasBlock(Placement::BEFORE_EVERY_LAST_POST->name()))
			return;

		if ($this->isBeforeEveryLastPost($context)) {
			$this->showBlocks(Placement::BEFORE_EVERY_LAST_POST->name());
		}
	}

	private function isBeforeEveryLastPost(array $context): bool
	{
		$counter = $context['counter'];
		$messagesPerPage = Utils::$context['messages_per_page'];

		if ($context['show_oldest_first']) {
			return $counter === Utils::$context['total_visible_posts'] || $counter % $messagesPerPage === 0;
		}

		return $context['output']['id'] === Utils::$context['topic_first_message']
			|| (Utils::$context['total_visible_posts'] - $counter) % $messagesPerPage === 0;
	}

	private function handleBeforeLastPost(array $context): void
	{
		if (! $this->hasBlock(Placement::BEFORE_LAST_POST->name()))
			return;

		$targetMessage = $context['show_oldest_first']
			? Utils::$context['topic_last_message']
			: Utils::$context['topic_first_message'];

		if ($context['output']['id'] === $targetMessage) {
			$this->showBlocks(Placement::BEFORE_LAST_POST->name());
		}
	}

	private function handleAfterEveryLastPost(array $context): void
	{
		if (! $this->hasBlock(Placement::AFTER_EVERY_LAST_POST->name()))
			return;

		$counter = $context['counter'];
		$messagesPerPage = Utils::$context['messages_per_page'];

		if ($counter === Utils::$context['total_visible_posts'] || $counter % $messagesPerPage === 0) {
			$this->injectBlockWithJs(
				$context['output']['id'],
				Placement::AFTER_EVERY_LAST_POST->name(),
				'afterend',
				'quickModForm > div.windowbg:last-of-type'
			);
		}
	}

	private function handleAfterLastPost(array $context): void
	{
		if (! $this->hasBlock(Placement::AFTER_LAST_POST->name()))
			return;

		$targetMessage = $context['show_oldest_first']
			? Utils::$context['topic_last_message']
			: Utils::$context['topic_first_message'];

		if ($context['output']['id'] === $targetMessage) {
			$this->injectBlockWithJs(
				$context['output']['id'],
				Placement::AFTER_LAST_POST->name(),
				'beforeend',
				'quickModForm',
				true
			);
		}
	}

	private function injectBlockWithJs(
		string $postId,
		string $placement,
		string $position,
		string $selector,
		bool $useId = false
	): void {
		$blockContent   = $this->captureBlockOutput($placement);
		$uniqueVar      = $this->generateUniqueVar($postId, $position);
		$selectorMethod = $useId ? 'getElementById' : 'querySelector';
		$selectorValue  = $useId ? "\"$selector\"" : "\"#$selector\"";

		$script = sprintf(
			'let %s = document.%s(%s);
			if (%s) {
				%s.insertAdjacentHTML("%s", %s);
			}',
			$uniqueVar,
			$selectorMethod,
			$selectorValue,
			$uniqueVar,
			$uniqueVar,
			$position,
			Utils::escapeJavaScript($blockContent)
		);

		Theme::addInlineJavaScript($script, true);
	}

	private function captureBlockOutput(string $placement): string
	{
		ob_start();

		$this->showBlocks($placement);

		return ob_get_clean();
	}

	private function generateUniqueVar(string $postId, string $position): string
	{
		$prefix = ucfirst(str_replace('end', '', $position));

		return "quickModForm$prefix$postId";
	}
}

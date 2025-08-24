<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.08.25
 */

namespace Bugo\LightPortal\Plugins\AdsBlock\Hooks;

use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\AdsBlock\Placement;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function explode;
use function strtotime;
use function time;

class MenuButtons
{
	use HasRequest;

	public function __invoke(): void
	{
		Utils::$context['lp_block_placements']['ads'] = Lang::$txt['lp_ads_block']['ads_type'];

		$this->prepareAdsPlacements();

		if ((empty(Utils::$context['current_board']) && empty(Utils::$context['lp_page'])))
			return;

		if ($this->request()->is('xml'))
			return;

		Utils::$context['lp_ads_blocks'] = $this->getData();

		if (Utils::$context['lp_ads_blocks']) {
			Utils::$context['lp_blocks'] = array_merge(Utils::$context['lp_blocks'], Utils::$context['lp_ads_blocks']);
		}

		$this->handleBlocks();
	}

	private function prepareAdsPlacements(): void
	{
		if ($this->request()->hasNot('area'))
			return;

		if ($this->request()->get('area') !== 'lp_settings')
			return;

		if (Utils::$context['current_subaction'] !== 'panels')
			return;

		unset(Utils::$context['lp_block_placements']['ads']);

		Utils::$context['lp_block_placements'] = array_merge(
			Utils::$context['lp_block_placements'], Placement::all()
		);
	}

	private function getData(): array
	{
		if (empty(Utils::$context['lp_blocks']['ads']))
			return [];

		$blocks = [];
		foreach (array_keys(Placement::all()) as $position) {
			$blocks[$position] = $this->getByPosition($position);
		}

		return $blocks;
	}

	private function handleBlocks(): void
	{
		if (empty(Utils::$context['lp_blocks']['ads']))
			return;

		foreach (Utils::$context['lp_blocks']['ads'] as $block) {
			if (empty($block['parameters']))
				continue;

			if (! empty($block['parameters']['loader_code'])) {
				Utils::$context['html_headers'] .= "\n\t" . $block['parameters']['loader_code'];
			}

			if (! empty($block['parameters']['end_date']) && $this->getEndTime($block['parameters']) <= time()) {
				$this->disableBlock($block['id']);
			}
		}
	}

	private function getByPosition(string $position): array
	{
		if (empty($position))
			return [];

		return array_filter(
			Utils::$context['lp_blocks']['ads'],
			fn($block) => (
				$this->filterByIncludedTopics($block) &&
				$this->filterByIncludedBoards($block) &&
				$this->filterByIncludedPages($block) &&
				$this->filterByAdsPlacement($position, $block)
			)
		);
	}

	private function filterByIncludedTopics(array $block): bool
	{
		if (! empty($block['parameters']['include_topics']) && ! empty(Utils::$context['current_topic'])) {
			$topics = array_flip(explode(',', (string) $block['parameters']['include_topics']));

			if (! array_key_exists(Utils::$context['current_topic'], $topics)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedBoards(array $block): bool
	{
		if (
			! empty($block['parameters']['include_boards'])
			&& ! empty(Utils::$context['current_board'])
			&& empty(Utils::$context['current_topic'])
		) {
			$boards = array_flip(explode(',', (string) $block['parameters']['include_boards']));

			if (! array_key_exists(Utils::$context['current_board'], $boards)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedPages(array $block): bool
	{
		if (! empty($block['parameters']['include_pages']) && ! empty(Utils::$context['lp_page'])) {
			$pages = array_flip(explode(',', (string) $block['parameters']['include_pages']));

			if (! array_key_exists(Utils::$context['lp_page']['id'], $pages)) {
				return false;
			}
		}

		return true;
	}

	private function filterByAdsPlacement(string $position, array $block): bool
	{
		if ($block['parameters']['ads_placement']) {
			$placements = array_flip(explode(',', (string) $block['parameters']['ads_placement']));

			if (! array_key_exists($position, $placements)) {
				return false;
			}
		}

		return true;
	}

	private function getEndTime(array $params): int
	{
		return $params['end_date'] ? strtotime((string) $params['end_date']) : time();
	}

	private function disableBlock(int $item): void
	{
		Db::$db->query('
			UPDATE {db_prefix}lp_blocks
			SET status = {int:status}
			WHERE block_id = {int:item}',
			[
				'status' => 0,
				'item'   => $item,
			]
		);
	}
}

<?php

/**
 * AdsBlock.php
 *
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.01.24
 */

namespace Bugo\LightPortal\Addons\AdsBlock;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CustomField, TextareaField, TextField};
use Bugo\LightPortal\Areas\Partials\{PageSelect, BoardSelect, TopicSelect};
use Bugo\LightPortal\Utils\{Lang, Theme, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class AdsBlock extends Block
{
	public string $icon = 'fas fa-ad';

	public function addSettings(array &$config_vars): void
	{
		$config_vars['ads_block'][] = ['range', 'min_replies'];
	}

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'ads_block')
			return;

		$params = [
			'content'        => 'html',
			'loader_code'    => '',
			'ads_placement'  => '',
			'include_boards' => '',
			'include_topics' => '',
			'include_pages'  => '',
			'end_date'       => '',
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'ads_block')
			return;

		$params = [
			'loader_code'    => FILTER_UNSAFE_RAW,
			'ads_placement'  => FILTER_DEFAULT,
			'include_boards' => FILTER_DEFAULT,
			'include_topics' => FILTER_DEFAULT,
			'include_pages'  => FILTER_DEFAULT,
			'end_date'       => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'ads_block')
			return;

		Theme::addInlineCss('
		.pf_placement, .pf_areas {
			display: none;
		}');

		TextareaField::make('loader_code', Lang::$txt['lp_ads_block']['loader_code'])
			->setTab('content')
			->setValue(Utils::$context['lp_block']['options']['loader_code']);

		TextField::make('placement', '')
			->setTab('content')
			->setAttribute('class', 'hidden')
			->setValue('ads');

		TextField::make('areas', '')
			->setTab('content')
			->setAttribute('class', 'hidden')
			->setValue('all');

		CustomField::make('ads_placement', Lang::$txt['lp_block_placement'])
			->setTab('access_placement')
			->setValue(fn() => new PlacementSelect, [
				'data'  => $this->getPlacements(),
				'value' => Utils::$context['lp_block']['options']['ads_placement']
			]);

		CustomField::make('include_boards', Lang::$txt['lp_ads_block']['include_boards'])
			->setTab('access_placement')
			->setValue(fn() => new BoardSelect, [
				'id'    => 'include_boards',
				'hint'  => Lang::$txt['lp_ads_block']['include_boards_select'],
				'value' => Utils::$context['lp_block']['options']['include_boards'] ?? '',
			]);

		CustomField::make('include_topics', Lang::$txt['lp_ads_block']['include_topics'])
			->setTab('access_placement')
			->setValue(fn() => new TopicSelect, [
				'id'    => 'include_pages',
				'hint'  => Lang::$txt['lp_ads_block']['include_pages_select'],
				'value' => Utils::$context['lp_block']['options']['include_pages'] ?? '',
			]);

		CustomField::make('include_pages', Lang::$txt['lp_ads_block']['include_pages'])
			->setTab('access_placement')
			->setValue(fn() => new PageSelect, [
				'id'    => 'include_pages',
				'hint'  => Lang::$txt['lp_ads_block']['include_pages_select'],
				'value' => Utils::$context['lp_block']['options']['include_pages'] ?? '',
			]);

		CustomField::make('end_date', Lang::$txt['lp_ads_block']['end_date'])
			->setValue('
			<input type="date" id="end_date" name="end_date" min="' . date('Y-m-d') . '" value="' . Utils::$context['lp_block']['options']['end_date'] . '">');
	}

	public function findBlockErrors(array &$post_errors, array $data): void
	{
		if ($data['placement'] !== 'ads')
			return;

		Lang::$txt['lp_post_error_no_ads_placement'] = Lang::$txt['lp_ads_block']['no_ads_placement'];

		if (empty($data['parameters']['ads_placement']))
			$post_errors[] = 'no_ads_placement';
	}

	public function parseContent(string &$content, string $type): void
	{
		if ($type === 'ads_block')
			$content = parse_content($content, 'html');
	}

	public function init(): void
	{
		if (! function_exists('lp_show_blocks'))
			Theme::loadTemplate('LightPortal/ViewBlocks');

		$this->applyHook('menu_buttons');
		$this->applyHook('admin_areas');
		$this->applyHook('messageindex_buttons');
		$this->applyHook('display_buttons');
		$this->applyHook('prepare_display_context');
	}

	/**
	 * Fetch info about all ads blocks
	 *
	 * Собираем информацию обо всех рекламных блоках
	 *
	 * @hook integrate_menu_buttons
	 */
	public function menuButtons(): void
	{
		Utils::$context['lp_block_placements']['ads'] = Lang::$txt['lp_ads_block']['ads_type'];

		if ((empty(Utils::$context['current_board']) && empty(Utils::$context['lp_page'])) || $this->request()->is('xml'))
			return;

		Utils::$context['lp_ads_blocks'] = $this->getData();

		if (Utils::$context['lp_ads_blocks'])
			Utils::$context['lp_blocks'] = array_merge(Utils::$context['lp_blocks'], Utils::$context['lp_ads_blocks']);

		if (! empty(Utils::$context['lp_blocks']['ads'])) {
			foreach (Utils::$context['lp_blocks']['ads'] as $block) {
				if ($block['parameters'] && ! empty($block['parameters']['loader_code'])) {
					Utils::$context['html_headers'] .= "\n\t" . $block['parameters']['loader_code'];
				}

				if ($block['parameters'] && ! empty($block['parameters']['end_date'])) {
					if ($this->getEndTime($block['parameters']) <= time()) {
						$this->disableBlock($block['id']);
					}
				}
			}
		}
	}

	public function adminAreas(): void
	{
		if ($this->request()->has('area') && $this->request('area') === 'lp_blocks')
			$this->setTemplate()->withLayer('ads_block_form');

		if ($this->request()->has('area') && $this->request('area') === 'lp_settings' && Utils::$context['current_subaction'] === 'panels') {
			unset(Utils::$context['lp_block_placements']['ads']);

			Utils::$context['lp_block_placements'] = array_merge(Utils::$context['lp_block_placements'], $this->getPlacements());
		}
	}

	/**
	 * Display ads within boards
	 *
	 * Отображаем рекламу в разделах
	 *
	 * @hook integrate_messageindex_buttons
	 */
	public function messageindexButtons(): void
	{
		$this->setTemplate()->withLayer('ads_placement_board');
	}

	/**
	 * Display ads within topics
	 *
	 * Отображаем рекламу в темах
	 *
	 * @hook integrate_display_buttons
	 */
	public function displayButtons(): void
	{
		if ($this->isTopicNumRepliesLesserThanMinReplies())
			return;

		$this->setTemplate()->withLayer('ads_placement_topic');
	}

	/**
	 * Display ads within portal pages
	 *
	 * Отображаем рекламу на страницах портала
	 *
	 * @hook preparePageData (portal)
	 */
	public function preparePageData(): void
	{
		$this->setTemplate()->withLayer('ads_placement_page');
	}

	/**
	 * Display ads within posts
	 *
	 * Отображаем рекламу в сообщениях
	 *
	 * @hook integrate_prepare_display_context
	 */
	public function prepareDisplayContext(array $output): void
	{
		if (empty(Utils::$context['lp_ads_blocks']) || ($this->isTopicNumRepliesLesserThanMinReplies()))
			return;

		$showOldestFirst = empty(Theme::$current->options['view_newest_first']);

		$counter = $output['counter'] + 1;

		$current_counter = $showOldestFirst ? Utils::$context['start'] : Utils::$context['total_visible_posts'] - Utils::$context['start'];

		/**
		 * Display ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if (Utils::$context['lp_ads_blocks']['before_first_post'] && $current_counter == $output['counter'] && empty(Utils::$context['start'])) {
			lp_show_blocks('before_first_post');
		}

		/**
		 * Display ads before each first message on the page
		 *
		 * Вывод рекламы перед каждым первым сообщением
		 */
		if (Utils::$context['lp_ads_blocks']['before_every_first_post'] && $current_counter == $output['counter']) {
			lp_show_blocks('before_every_first_post');
		}

		/**
		 * Display ads after the first message
		 *
		 * Вывод рекламы после первого сообщения
		 */
		if (Utils::$context['lp_ads_blocks']['after_first_post'] && ($counter == ($showOldestFirst ? 2 : Utils::$context['total_visible_posts'] - 2))) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Display ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if (Utils::$context['lp_ads_blocks']['after_every_first_post'] && ($output['counter'] == ($showOldestFirst ? Utils::$context['start'] + 1 : $current_counter - 1))) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Display ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		$before_every_last_post = $showOldestFirst
			? $counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0
			: ($output['id'] == Utils::$context['topic_first_message'] || (Utils::$context['total_visible_posts'] - $counter) % Utils::$context['messages_per_page'] == 0);
		if (Utils::$context['lp_ads_blocks']['before_every_last_post'] && $before_every_last_post) {
			lp_show_blocks('before_every_last_post');
		}

		/**
		 * Display ads before the last message
		 *
		 * Вывод рекламы перед последним сообщением
		 */
		if (Utils::$context['lp_ads_blocks']['before_last_post'] && $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])) {
			lp_show_blocks('before_last_post');
		}

		/**
		 * Display ads after each last message on the page
		 *
		 * Вывод рекламы после каждого последнего сообщения
		 */
		if (Utils::$context['lp_ads_blocks']['after_every_last_post'] && ($counter == Utils::$context['total_visible_posts'] || $counter % Utils::$context['messages_per_page'] == 0)) {
			ob_start();

			lp_show_blocks('after_every_last_post');

			$after_every_last_post = ob_get_clean();

			Theme::addInlineJS('
		jQuery(document).ready(function ($) {
			$(' . Utils::escapeJavaScript($after_every_last_post) . ').insertAfter("#quickModForm > div.windowbg:last");
		});', true);
		}

		/**
		 * Display ads after the last message
		 *
		 * Вывод рекламы после последнего сообщения
		 */
		if (Utils::$context['lp_ads_blocks']['after_last_post'] && $output['id'] == ($showOldestFirst ? Utils::$context['topic_last_message'] : Utils::$context['topic_first_message'])) {
			ob_start();

			lp_show_blocks('after_last_post');

			$after_last_post = ob_get_clean();

			Theme::addInlineJS('
		jQuery(document).ready(function ($) {
			$("#quickModForm").append(' . Utils::escapeJavaScript($after_last_post) . ');
		});', true);
		}
	}

	public function getData(): array
	{
		if (empty(Utils::$context['lp_blocks']['ads']))
			return [];

		$ads_blocks = [];
		$placement_set = $this->getPlacements();
		foreach (array_keys($placement_set) as $position) {
			$ads_blocks[$position] = $this->getByPosition($position);
		}

		return $ads_blocks;
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
			$topics = array_flip(explode(',', $block['parameters']['include_topics']));

			if (! array_key_exists(Utils::$context['current_topic'], $topics)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedBoards(array $block): bool
	{
		if (! empty($block['parameters']['include_boards']) && ! empty(Utils::$context['current_board']) && empty(Utils::$context['current_topic'])) {
			$boards = array_flip(explode(',', $block['parameters']['include_boards']));

			if (! array_key_exists(Utils::$context['current_board'], $boards)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedPages(array $block): bool
	{
		if (! empty($block['parameters']['include_pages']) && ! empty(Utils::$context['lp_page'])) {
			$pages = array_flip(explode(',', $block['parameters']['include_pages']));

			if (! array_key_exists(Utils::$context['lp_page']['id'], $pages)) {
				return false;
			}
		}

		return true;
	}

	private function filterByAdsPlacement(string $position, array $block): bool
	{
		if ($block['parameters']['ads_placement']) {
			$placements = array_flip(explode(',', $block['parameters']['ads_placement']));

			if (! array_key_exists($position, $placements)) {
				return false;
			}
		}

		return true;
	}

	private function getPlacements(): array
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

	private function getEndTime(array $params): int
	{
		$end_time = time();

		if ($params['end_date'])
			$end_time = strtotime($params['end_date']);

		return $end_time;
	}

	private function disableBlock(int $item): void
	{
		Utils::$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET status = {int:status}
			WHERE block_id = {int:item}',
			[
				'status' => 0,
				'item'   => $item
			]
		);

		Utils::$context['lp_num_queries']++;
	}

	private function isTopicNumRepliesLesserThanMinReplies(): bool
	{
		return isset(Utils::$context['lp_ads_block_plugin']['min_replies'])
			&& Utils::$context['topicinfo']['num_replies'] < (int) Utils::$context['lp_ads_block_plugin']['min_replies'];
	}
}

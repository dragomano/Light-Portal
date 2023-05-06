<?php

/**
 * AdsBlock.php
 *
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 1.05.23
 */

namespace Bugo\LightPortal\Addons\AdsBlock;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\{BoardSelect, PageSelect, TopicSelect};

if (! defined('LP_NAME'))
	die('No direct access...');

class AdsBlock extends Block
{
	public string $icon = 'fas fa-ad';

	public function addSettings(array &$config_vars)
	{
		$config_vars['ads_block'][] = ['int', 'min_replies'];
	}

	public function blockOptions(array &$options)
	{
		$options['ads_block']['content'] = 'html';

		$options['ads_block']['parameters'] = [
			'loader_code'    => '',
			'ads_placement'  => '',
			'include_boards' => '',
			'include_topics' => '',
			'include_pages'  => '',
			'end_date'       => '',
		];
	}

	public function parseContent(string &$content, string $type)
	{
		if ($type === 'ads_block')
			$content = parse_content($content, 'html');
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'ads_block')
			return;

		$parameters['loader_code']    = FILTER_UNSAFE_RAW;
		$parameters['ads_placement']  = FILTER_DEFAULT;
		$parameters['include_boards'] = FILTER_DEFAULT;
		$parameters['include_topics'] = FILTER_DEFAULT;
		$parameters['include_pages']  = FILTER_DEFAULT;
		$parameters['end_date']       = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'ads_block')
			return;

		$this->context['posting_fields']['loader_code']['label']['text'] = $this->txt['lp_ads_block']['loader_code'];
		$this->context['posting_fields']['loader_code']['input'] = [
			'type' => 'textarea',
			'attributes' => [
				'id'    => 'loader_code',
				'value' => $this->context['lp_block']['options']['parameters']['loader_code']
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['placement']['label']['text'] = '';
		$this->context['posting_fields']['placement']['input'] = [
			'type' => 'text',
			'attributes' => [
				'maxlength' => 255,
				'value'     => 'ads',
				'required'  => true,
				'style'     => 'display: none'
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['areas']['label']['text'] = '';
		$this->context['posting_fields']['areas']['input'] = [
			'type' => 'text',
			'attributes' => [
				'maxlength' => 255,
				'value'     => 'all',
				'required'  => true,
				'style'     => 'display: none'
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['ads_placement']['label']['html'] = $this->txt['lp_block_placement'];
		$this->context['posting_fields']['ads_placement']['input']['tab']  = 'access_placement';
		$this->context['posting_fields']['ads_placement']['input']['html'] = (new PlacementSelect)([
			'data'  => $this->getPlacements(),
			'value' => $this->context['lp_block']['options']['parameters']['ads_placement']
		]);

		$this->context['posting_fields']['include_boards']['label']['html'] = $this->txt['lp_ads_block']['include_boards'];
		$this->context['posting_fields']['include_boards']['input']['tab'] = 'access_placement';
		$this->context['posting_fields']['include_boards']['input']['html'] = (new BoardSelect)([
			'id'    => 'include_boards',
			'hint'  => $this->txt['lp_ads_block']['include_boards_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_boards'] ?? '',
		]);

		$this->context['posting_fields']['include_topics']['label']['html'] = $this->txt['lp_ads_block']['include_topics'];
		$this->context['posting_fields']['include_topics']['input']['tab'] = 'access_placement';
		$this->context['posting_fields']['include_topics']['input']['html'] = (new TopicSelect)([
			'id'    => 'include_topics',
			'hint'  => $this->txt['lp_ads_block']['include_topics_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_topics'] ?? '',
		]);

		$this->context['posting_fields']['include_pages']['label']['html'] = $this->txt['lp_ads_block']['include_pages'];
		$this->context['posting_fields']['include_pages']['input']['tab'] = 'access_placement';
		$this->context['posting_fields']['include_pages']['input']['html'] = (new PageSelect)([
			'id'    => 'include_pages',
			'hint'  => $this->txt['lp_ads_block']['include_pages_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_pages'] ?? '',
		]);

		$this->context['posting_fields']['end_date']['label']['html'] = $this->txt['lp_ads_block']['end_date'];
		$this->context['posting_fields']['end_date']['input']['html'] = '
			<input type="date" id="end_date" name="end_date" min="' . date('Y-m-d') . '" value="' . $this->context['lp_block']['options']['parameters']['end_date'] . '">';
	}

	public function findBlockErrors(array $data, array &$post_errors)
	{
		if ($data['placement'] !== 'ads')
			return;

		$this->txt['lp_post_error_no_ads_placement'] = $this->txt['lp_ads_block']['no_ads_placement'];

		if (empty($data['parameters']['ads_placement']))
			$post_errors[] = 'no_ads_placement';
	}

	public function init()
	{
		if (! function_exists('lp_show_blocks'))
			$this->loadTemplate('LightPortal/ViewBlocks');

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
	public function menuButtons()
	{
		$this->context['lp_block_placements']['ads'] = $this->txt['lp_ads_block']['ads_type'];

		if ((empty($this->context['current_board']) && empty($this->context['lp_page'])) || $this->request()->is('xml'))
			return;

		$this->context['lp_ads_blocks'] = $this->getData();

		if ($this->context['lp_ads_blocks'])
			$this->context['lp_blocks'] = array_merge($this->context['lp_blocks'], $this->context['lp_ads_blocks']);

		if (! empty($this->context['lp_blocks']['ads'])) {
			foreach ($this->context['lp_blocks']['ads'] as $block) {
				if ($block['parameters'] && ! empty($block['parameters']['loader_code'])) {
					$this->context['html_headers'] .= "\n\t" . $block['parameters']['loader_code'];
				}

				if ($block['parameters'] && ! empty($block['parameters']['end_date'])) {
					if ($this->getEndTime($block['parameters']) <= time()) {
						$this->disableBlock($block['id']);
					}
				}
			}
		}
	}

	public function adminAreas()
	{
		if ($this->request()->has('area') && $this->request('area') === 'lp_blocks')
			$this->setTemplate()->withLayer('ads_block_form');

		if ($this->request()->has('area') && $this->request('area') === 'lp_settings' && $this->context['current_subaction'] === 'panels') {
			unset($this->context['lp_block_placements']['ads']);

			$this->context['lp_block_placements'] = array_merge($this->context['lp_block_placements'], $this->getPlacements());
		}
	}

	/**
	 * Display ads within boards
	 *
	 * Отображаем рекламу в разделах
	 *
	 * @hook integrate_messageindex_buttons
	 */
	public function messageindexButtons()
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
	public function displayButtons()
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
	public function preparePageData()
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
	public function prepareDisplayContext(array $output, array &$message, int $counter)
	{
		if (empty($this->context['lp_ads_blocks']) || ($this->isTopicNumRepliesLesserThanMinReplies()))
			return;

		$showOldestFirst = empty($this->options['view_newest_first']);

		$current_counter = $showOldestFirst ? $this->context['start'] : $this->context['total_visible_posts'] - $this->context['start'];

		/**
		 * Display ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if ($this->context['lp_ads_blocks']['before_first_post'] && $current_counter == $output['counter'] && empty($this->context['start'])) {
			lp_show_blocks('before_first_post');
		}

		/**
		 * Display ads before each first message on the page
		 *
		 * Вывод рекламы перед каждым первым сообщением
		 */
		if ($this->context['lp_ads_blocks']['before_every_first_post'] && $current_counter == $output['counter']) {
			lp_show_blocks('before_every_first_post');
		}

		/**
		 * Display ads after the first message
		 *
		 * Вывод рекламы после первого сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_first_post'] && ($counter == ($showOldestFirst ? 2 : $this->context['total_visible_posts'] - 2))) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Display ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_every_first_post'] && ($output['counter'] == ($showOldestFirst ? $this->context['start'] + 1 : $current_counter - 1))) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Display ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		$before_every_last_post = $showOldestFirst
			? $counter == $this->context['total_visible_posts'] || $counter % $this->context['messages_per_page'] == 0
			: ($output['id'] == $this->context['topic_first_message'] || ($this->context['total_visible_posts'] - $counter) % $this->context['messages_per_page'] == 0);
		if ($this->context['lp_ads_blocks']['before_every_last_post'] && $before_every_last_post) {
			lp_show_blocks('before_every_last_post');
		}

		/**
		 * Display ads before the last message
		 *
		 * Вывод рекламы перед последним сообщением
		 */
		if ($this->context['lp_ads_blocks']['before_last_post'] && $output['id'] == ($showOldestFirst ? $this->context['topic_last_message'] : $this->context['topic_first_message'])) {
			lp_show_blocks('before_last_post');
		}

		/**
		 * Display ads after each last message on the page
		 *
		 * Вывод рекламы после каждого последнего сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_every_last_post'] && ($counter == $this->context['total_visible_posts'] || $counter % $this->context['messages_per_page'] == 0)) {
			ob_start();

			lp_show_blocks('after_every_last_post');

			$after_every_last_post = ob_get_clean();

			$this->addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$(' . $this->jsEscape($after_every_last_post) . ').insertAfter("#quickModForm > div.windowbg:last");
		});', true);
		}

		/**
		 * Display ads after the last message
		 *
		 * Вывод рекламы после последнего сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_last_post'] && $output['id'] == ($showOldestFirst ? $this->context['topic_last_message'] : $this->context['topic_first_message'])) {
			ob_start();

			lp_show_blocks('after_last_post');

			$after_last_post = ob_get_clean();

			$this->addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$("#quickModForm").append(' . $this->jsEscape($after_last_post) . ');
		});', true);
		}
	}

	public function getData(): array
	{
		if (empty($this->context['lp_blocks']['ads']))
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
			$this->context['lp_blocks']['ads'],
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
		if (! empty($block['parameters']['include_topics']) && ! empty($this->context['current_topic'])) {
			$topics = array_flip(explode(',', $block['parameters']['include_topics']));

			if (! array_key_exists($this->context['current_topic'], $topics)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedBoards(array $block): bool
	{
		if (! empty($block['parameters']['include_boards']) && ! empty($this->context['current_board']) && empty($this->context['current_topic'])) {
			$boards = array_flip(explode(',', $block['parameters']['include_boards']));

			if (! array_key_exists($this->context['current_board'], $boards)) {
				return false;
			}
		}

		return true;
	}

	private function filterByIncludedPages(array $block): bool
	{
		if (! empty($block['parameters']['include_pages']) && ! empty($this->context['lp_page'])) {
			$pages = array_flip(explode(',', $block['parameters']['include_pages']));

			if (! array_key_exists($this->context['lp_page']['id'], $pages)) {
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
			$this->txt['lp_ads_block']['placement_set']
		);
	}

	private function getEndTime(array $params): int
	{
		$end_time = time();

		if ($params['end_date'])
			$end_time = strtotime($params['end_date']);

		return $end_time;
	}

	private function disableBlock(int $item)
	{
		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET status = {int:status}
			WHERE block_id = {int:item}',
			[
				'status' => 0,
				'item'   => $item
			]
		);

		$this->context['lp_num_queries']++;
	}

	private function isTopicNumRepliesLesserThanMinReplies(): bool
	{
		return isset($this->context['lp_ads_block_plugin']['min_replies'])
			&& $this->context['topicinfo']['num_replies'] < (int) $this->context['lp_ads_block_plugin']['min_replies'];
	}
}

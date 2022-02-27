<?php

/**
 * AdsBlock.php
 *
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 27.02.22
 */

namespace Bugo\LightPortal\Addons\AdsBlock;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class AdsBlock extends Plugin
{
	public string $icon = 'fas fa-ad';

	public function addSettings(array &$config_vars)
	{
		$config_vars['ads_block'][] = ['int', 'min_replies'];
	}

	/**
	 * Add advertising areas to panel settings
	 *
	 * Добавляем рекламные области в настройки панелей
	 *
	 * @hook
	 */
	public function addPanelsSettings()
	{
		unset($this->context['lp_block_placements']['ads']);

		$this->context['lp_block_placements'] = array_merge($this->context['lp_block_placements'], $this->getPlacements());
	}

	public function blockOptions(array &$options)
	{
		$options['ads_block']['content'] = 'html';

		$options['ads_block']['parameters'] = [
			'loader_code'     => '',
			'ads_placement'   => '',
			'included_boards' => '',
			'included_topics' => '',
			'end_date'        => '',
			'end_time'        => '',//$this->getDateTime()->format('H:i')
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

		$parameters['loader_code'] = FILTER_UNSAFE_RAW;
		$parameters['ads_placement'] = [
			'name'   => 'ads_placement',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		];
		$parameters['included_boards'] = FILTER_DEFAULT;
		$parameters['included_topics'] = FILTER_DEFAULT;
		$parameters['end_date']        = FILTER_DEFAULT;
		$parameters['end_time']        = FILTER_DEFAULT;
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

		$this->context['posting_fields']['ads_placement']['label']['text'] = $this->txt['lp_block_placement'];
		$this->context['posting_fields']['ads_placement']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id'       => 'ads_placement',
				'name'     => 'ads_placement[]',
				'multiple' => true,
				'style'    => 'height: auto'
			],
			'options' => [],
			'tab' => 'access_placement'
		];

		$this->context['posting_fields']['included_boards']['label']['text'] = $this->txt['lp_ads_block']['included_boards'];
		$this->context['posting_fields']['included_boards']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_ads_block']['included_boards_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['included_boards'] ?? '',
				'style'     => 'width: 100%'
			],
			'tab' => 'access_placement'
		];

		$this->context['posting_fields']['included_topics']['label']['text'] = $this->txt['lp_ads_block']['included_topics'];
		$this->context['posting_fields']['included_topics']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_ads_block']['included_topics_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['options']['parameters']['included_topics'] ?? '',
				'style'     => 'width: 100%'
			],
			'tab' => 'access_placement'
		];

		$this->context['posting_fields']['end_time']['label']['html'] = '<label for="end_date">' . $this->txt['lp_ads_block']['end_time'] . '</label>';
		$this->context['posting_fields']['end_time']['input']['html'] = '
			<input type="date" id="end_date" name="end_date" min="' . date('Y-m-d') . '" value="' . $this->context['lp_block']['options']['parameters']['end_date'] . '">
			<input type="time" name="end_time" value="' . $this->context['lp_block']['options']['parameters']['end_time'] . '">';

		$this->context['ads_placements'] = $this->getPlacements();

		$this->loadTemplate()->withLayer('ads_block');
	}

	/**
	 * @hook
	 */
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
			loadTemplate('LightPortal/ViewBlock');

		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
		add_integration_function('integrate_messageindex_buttons', __CLASS__ . '::messageindexButtons#', false, __FILE__);
		add_integration_function('integrate_display_buttons', __CLASS__ . '::displayButtons#', false, __FILE__);
		add_integration_function('integrate_prepare_display_context', __CLASS__ . '::prepareDisplayContext#', false, __FILE__);
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

		if ($this->request()->is('admin') && $this->request()->has('area') && $this->request('area') === 'lp_blocks')
			$this->loadTemplate()->withLayer('ads_block_form');

		if (empty($this->context['current_board']) || $this->request()->is('xml'))
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

	/**
	 * Display ads within boards
	 *
	 * Отображаем рекламу в разделах
	 *
	 * @hook integrate_messageindex_buttons
	 */
	public function messageindexButtons()
	{
		$this->loadTemplate()->withLayer('ads_placement_board');
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

		$this->loadTemplate()->withLayer('ads_placement_topic');
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

		$current_counter = empty($this->options['view_newest_first']) ? $this->context['start'] : $this->context['total_visible_posts'] - $this->context['start'];

		/**
		 * Display ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if ($this->context['lp_ads_blocks']['before_first_post'] && $current_counter == $output['counter'] &&
			empty($this->context['start'])) {
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
		if ($this->context['lp_ads_blocks']['after_first_post'] && ($counter == (empty($this->options['view_newest_first']) ? 2 : $this->context['total_visible_posts'] - 2))) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Display ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_every_first_post'] && ($output['counter'] == (empty($this->options['view_newest_first']) ? $this->context['start'] + 1 : $current_counter - 1))) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Display ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		$before_every_last_post = empty($this->options['view_newest_first'])
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
		if ($this->context['lp_ads_blocks']['before_last_post'] &&
			$output['id'] == (empty($this->options['view_newest_first']) ? $this->context['topic_last_message'] : $this->context['topic_first_message'])) {
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

			addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$(' . JavaScriptEscape($after_every_last_post) . ').insertAfter("#quickModForm > div.windowbg:last");
		});', true);
		}

		/**
		 * Display ads after the last message
		 *
		 * Вывод рекламы после последнего сообщения
		 */
		if ($this->context['lp_ads_blocks']['after_last_post'] &&
			$output['id'] == (empty($this->options['view_newest_first']) ? $this->context['topic_last_message'] : $this->context['topic_first_message'])) {
			ob_start();

			lp_show_blocks('after_last_post');

			$after_last_post = ob_get_clean();

			addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$("#quickModForm").append(' . JavaScriptEscape($after_last_post) . ');
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

		return array_filter($this->context['lp_blocks']['ads'], function ($block) use ($position) {
			if (! empty($block['parameters']['included_boards'])) {
				$boards = array_flip(explode(',', $block['parameters']['included_boards']));

				if (! array_key_exists($this->context['current_board'], $boards))
					return false;
			}

			if (! empty($block['parameters']['included_topics']) && ! empty($this->context['current_topic'])) {
				$topics = array_flip(explode(',', $block['parameters']['included_topics']));

				if (! array_key_exists($this->context['current_topic'], $topics))
					return false;
			}

			if ($block['parameters']['ads_placement']) {
				$placements = array_flip(explode(',', $block['parameters']['ads_placement']));

				return array_key_exists($position, $placements);
			}

			return false;
		});
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
				'after_last_post'
			],
			$this->txt['lp_ads_block']['placement_set']
		);
	}

	private function getEndTime(array $params): int
	{
		$end_time = time();

		if ($params['end_date'])
			$end_time = strtotime($params['end_date']);

		if ($params['end_time'])
			$end_time = strtotime(date('Y-m-d', $end_time) . ' ' . $params['end_time']);

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
		return isset($this->modSettings['lp_ads_block_addon_min_replies'])
			&& $this->context['topicinfo']['num_replies'] < (int) $this->modSettings['lp_ads_block_addon_min_replies'];
	}
}

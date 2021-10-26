<?php

/**
 * AdsBlock.php
 *
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\AdsBlock;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\{Helpers, ManageBlocks};

class AdsBlock extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-ad';

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(array &$config_vars)
	{
		$config_vars['ads_block'][] = array('int', 'min_replies');
	}

	/**
	 * Add advertising areas to panel settings
	 *
	 * Добавляем рекламные области в настройки панелей
	 *
	 * @return void
	 */
	public function addPanelsSettings()
	{
		global $context;

		unset($context['lp_block_placements']['ads']);

		$context['lp_block_placements'] = array_merge($context['lp_block_placements'], $this->getPlacements());
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['ads_block']['content'] = 'html';

		$options['ads_block']['parameters'] = [
			'loader_code'     => '',
			'ads_placement'   => '',
			'included_boards' => '',
			'included_topics' => '',
			'end_date'        => '',
			'end_time'        => date('H:i')
		];
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public function parseContent(string &$content, string $type)
	{
		if ($type == 'ads_block')
			Helpers::parseContent($content, 'html');
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'ads_block')
			return;

		$parameters['loader_code'] = FILTER_UNSAFE_RAW;
		$parameters['ads_placement'] = array(
			'name'   => 'ads_placement',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
		$parameters['included_boards'] = FILTER_SANITIZE_STRING;
		$parameters['included_topics'] = FILTER_SANITIZE_STRING;
		$parameters['end_date']        = FILTER_SANITIZE_STRING;
		$parameters['end_time']        = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'ads_block')
			return;

		$context['posting_fields']['loader_code']['label']['text'] = $txt['lp_ads_block']['loader_code'];
		$context['posting_fields']['loader_code']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
				'id'    => 'loader_code',
				'value' => $context['lp_block']['options']['parameters']['loader_code']
			),
			'tab' => 'content'
		);

		$context['posting_fields']['placement']['label']['text'] = '';
		$context['posting_fields']['placement']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => 'ads',
				'required'  => true,
				'style'     => 'display: none'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['areas']['label']['text'] = '';
		$context['posting_fields']['areas']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => 'all',
				'required'  => true,
				'style'     => 'display: none'
			),
			'tab' => 'content'
		);

		if (!is_array($context['lp_block']['options']['parameters']['ads_placement'])) {
			$context['lp_block']['options']['parameters']['ads_placement'] = explode(',', $context['lp_block']['options']['parameters']['ads_placement']);
		}

		$data = [];

		$placement_set = $this->getPlacements();
		foreach ($placement_set as $position => $title) {
			$data[] = "\t\t\t\t" . '{text: "' . $title . '", value: "' . $position . '", selected: ' . (in_array($position, $context['lp_block']['options']['parameters']['ads_placement']) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#ads_placement",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $txt['lp_ads_block']['select_placement'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$context['posting_fields']['ads_placement']['label']['text'] = $txt['lp_block_placement'];
		$context['posting_fields']['ads_placement']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'ads_placement',
				'name'     => 'ads_placement[]',
				'multiple' => true,
				'style'    => 'height: auto'
			),
			'options' => array(),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['included_boards']['label']['text'] = $txt['lp_ads_block']['included_boards'];
		$context['posting_fields']['included_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_ads_block']['included_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['included_boards'] ?? '',
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['included_topics']['label']['text'] = $txt['lp_ads_block']['included_topics'];
		$context['posting_fields']['included_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_ads_block']['included_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['included_topics'] ?? '',
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['end_time']['label']['html'] = '<label for="end_date">' . $txt['lp_ads_block']['end_time'] . '</label>';
		$context['posting_fields']['end_time']['input']['html'] = '
			<input type="date" id="end_date" name="end_date" min="' . date('Y-m-d') . '" value="' . $context['lp_block']['options']['parameters']['end_date'] . '">
			<input type="time" name="end_time" value="' . $context['lp_block']['options']['parameters']['end_time'] . '">';
	}

	/**
	 * @param array $data
	 * @param array $post_errors
	 * @return void
	 */
	public function findBlockErrors(array $data, array &$post_errors)
	{
		global $txt;

		if ($data['placement'] !== 'ads')
			return;

		$txt['lp_post_error_no_ads_placement'] = $txt['lp_ads_block']['no_ads_placement'];

		if (empty($data['parameters']['ads_placement']))
			$post_errors[] = 'no_ads_placement';
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if (!function_exists('lp_show_blocks'))
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
	 * @return void
	 */
	public function menuButtons()
	{
		global $context, $txt;

		$context['lp_block_placements']['ads'] = $txt['lp_ads_block']['ads_type'];

		if (Helpers::request()->is('admin') && Helpers::request()->has('area') && Helpers::request('area') === 'lp_blocks') {
			$this->loadTemplate();

			$context['template_layers'][] = 'ads_block';
		}

		if (empty($context['current_board']) || Helpers::request()->is('xml'))
			return;

		$context['lp_ads_blocks'] = $this->getData();

		if (!empty($context['lp_ads_blocks']))
			$context['lp_blocks'] = array_merge($context['lp_blocks'], $context['lp_ads_blocks']);

		if (!empty($context['lp_blocks']['ads'])) {
			foreach ($context['lp_blocks']['ads'] as $block) {
				if (!empty($block['parameters']) && !empty($block['parameters']['loader_code'])) {
					$context['html_headers'] .= "\n\t" . $block['parameters']['loader_code'];
				}

				if (!empty($block['parameters']) && !empty($block['parameters']['end_date'])) {
					if ($this->getEndTime($block['parameters']) <= time()) {
						ManageBlocks::toggleStatus([$block['id']]);
					}
				}
			}
		}
	}

	/**
	 * Display ads within boards
	 *
	 * Отображение рекламы в разделах
	 *
	 * @return void
	 */
	public function messageindexButtons()
	{
		global $context;

		$this->loadTemplate();

		$context['template_layers'][] = 'ads_placement_board';
	}

	/**
	 * Display ads within topics
	 *
	 * Отображение рекламы в темах
	 *
	 * @return void
	 */
	public function displayButtons()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_ads_block_addon_min_replies']) && $context['topicinfo']['num_replies'] < $modSettings['lp_ads_block_addon_min_replies'])
			return;

		$this->loadTemplate();

		$context['template_layers'][] = 'ads_placement_topic';
	}

	/**
	 * Display ads within posts
	 *
	 * Отображение рекламы в сообщениях
	 *
	 * @param array $output
	 * @param array $message
	 * @param int $counter
	 * @return void
	 */
	public function prepareDisplayContext(array &$output, array &$message, int $counter)
	{
		global $modSettings, $options, $context;

		if (!empty($modSettings['lp_ads_block_addon_min_replies']) && $context['topicinfo']['num_replies'] < $modSettings['lp_ads_block_addon_min_replies'])
			return;

		$current_counter = empty($options['view_newest_first']) ? $context['start'] : $context['total_visible_posts'] - $context['start'];

		/**
		 * Display ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_first_post']) && $current_counter == $output['counter'] && empty($context['start'])) {
			lp_show_blocks('before_first_post');
		}

		/**
		 * Display ads before each first message on the page
		 *
		 * Вывод рекламы перед каждым первым сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_every_first_post']) && $current_counter == $output['counter']) {
			lp_show_blocks('before_every_first_post');
		}

		/**
		 * Display ads after the first message
		 *
		 * Вывод рекламы после первого сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_first_post']) && ($counter == (empty($options['view_newest_first']) ? 2 : $context['total_visible_posts'] - 2))) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Display ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_every_first_post']) && ($output['counter'] == (empty($options['view_newest_first']) ? $context['start'] + 1 : $current_counter - 1))) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Display ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		$before_every_last_post = empty($options['view_newest_first'])
			? $counter == $context['total_visible_posts'] || $counter % $context['messages_per_page'] == 0
			: ($output['id'] == $context['topic_first_message'] || ($context['total_visible_posts'] - $counter) % $context['messages_per_page'] == 0);
		if (!empty($context['lp_ads_blocks']['before_every_last_post']) && $before_every_last_post) {
			lp_show_blocks('before_every_last_post');
		}

		/**
		 * Display ads before the last message
		 *
		 * Вывод рекламы перед последним сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_last_post']) &&
			$output['id'] == (empty($options['view_newest_first']) ? $context['topic_last_message'] : $context['topic_first_message'])) {
			lp_show_blocks('before_last_post');
		}

		/**
		 * Display ads after each last message on the page
		 *
		 * Вывод рекламы после каждого последнего сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_every_last_post']) && ($counter == $context['total_visible_posts'] || $counter % $context['messages_per_page'] == 0)) {
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
		if (!empty($context['lp_ads_blocks']['after_last_post']) &&
			$output['id'] == (empty($options['view_newest_first']) ? $context['topic_last_message'] : $context['topic_first_message'])) {
			ob_start();

			lp_show_blocks('after_last_post');

			$after_last_post = ob_get_clean();

			addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$("#quickModForm").append(' . JavaScriptEscape($after_last_post) . ');
		});', true);
		}
	}

	/**
	 * Get all ads blocks
	 *
	 * Получаем все рекламные блоки
	 *
	 * @return array
	 */
	public function getData(): array
	{
		global $context;

		if (empty($context['lp_blocks']['ads']))
			return [];

		$ads_blocks = [];
		$placement_set = $this->getPlacements();
		foreach (array_keys($placement_set) as $position) {
			$ads_blocks[$position] = $this->getByPosition($position);
		}

		return $ads_blocks;
	}

	/**
	 * @param string $position
	 * @return array
	 */
	private function getByPosition(string $position): array
	{
		global $context;

		if (empty($position))
			return [];

		return array_filter($context['lp_blocks']['ads'], function ($block) use ($position, $context) {
			if (!empty($block['parameters']['ads_boards'])) {
				$boards = array_flip(explode(',', $block['parameters']['ads_boards']));

				if (!array_key_exists($context['current_board'], $boards))
					return false;
			}

			if (!empty($block['parameters']['ads_topics']) && !empty($context['current_topic'])) {
				$topics = array_flip(explode(',', $block['parameters']['ads_topics']));

				if (!array_key_exists($context['current_topic'], $topics))
					return false;
			}

			if (!empty($block['parameters']['ads_placement'])) {
				$placements = array_flip(explode(',', $block['parameters']['ads_placement']));

				return array_key_exists($position, $placements);
			}

			return false;
		});
	}

	/**
	 * @return array
	 */
	private function getPlacements(): array
	{
		global $txt;

		return array_combine(
			array(
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
			),
			$txt['lp_ads_block']['placement_set']
		);
	}

	/**
	 * @param array $params
	 * @return int
	 */
	private function getEndTime(array $params): int
	{
		$end_time = time();

		if (!empty($params['end_date']))
			$end_time = strtotime($params['end_date']);

		if (!empty($params['end_time']))
			$end_time = strtotime(date('Y-m-d', $end_time) . ' ' . $params['end_time']);

		return $end_time;
	}
}

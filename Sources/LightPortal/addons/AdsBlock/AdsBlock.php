<?php

namespace Bugo\LightPortal\Addons\AdsBlock;

use Bugo\LightPortal\Helpers;

/**
 * AdsBlock
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class AdsBlock
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-ad';

	/**
	 * Default placement of ads block
	 *
	 * Размещение рекламного блока по умолчанию
	 *
	 * @var string
	 */
	private static $placement = '';

	/**
	 * Board ids for block output
	 *
	 * Идентификаторы разделов для вывода блока
	 *
	 * @var string
	 */
	private static $boards = '';

	/**
	 * Topic ids for block output
	 *
	 * Идентификаторы тем для вывода блока
	 *
	 * @var string
	 */
	private static $topics = '';

	/**
	 * Run necessary hooks
	 *
	 * Запускаем необходимые хуки
	 *
	 * @return void
	 */
	public static function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons', false, __FILE__);
		add_integration_function('integrate_messageindex_buttons', __CLASS__ . '::messageindexButtons', false, __FILE__);
		add_integration_function('integrate_display_buttons', __CLASS__ . '::displayButtons', false, __FILE__);
		add_integration_function('integrate_prepare_display_context', __CLASS__ . '::prepareDisplayContext', false, __FILE__);
	}

	/**
	 * Fetch info about all ads blocks
	 *
	 * Собираем информацию обо всех рекламных блоках
	 *
	 * @return void
	 */
	public static function menuButtons()
	{
		global $context;

		if ($context['current_action'] == 'admin' && isset($_REQUEST['area']) && $_REQUEST['area'] == 'lp_blocks') {
			require_once(__DIR__ . '/Template.php');
			ads_block_form();
		}

		if (empty($context['current_board']))
			return;

		$context['lp_ads_blocks'] = Helpers::getFromCache('ads_block_addon', 'getData', __CLASS__);

		if (!empty($context['lp_ads_blocks']))
			$context['lp_blocks'] = array_merge($context['lp_blocks'], $context['lp_ads_blocks']);

		if (!function_exists('lp_show_blocks'))
			loadTemplate('LightPortal/ViewBlock');
	}

	/**
	 * Get all ads blocks
	 *
	 * Получаем все рекламные блоки
	 *
	 * @return array
	 */
	public static function getData()
	{
		global $context, $txt;

		if (empty($context['lp_blocks']['ads']))
			return [];

		foreach ($txt['lp_ads_block_addon_placement_set'] as $position => $dump)
			$ads_blocks[$position] = self::getByPosition($position);

		return $ads_blocks;
	}

	/**
	 * Displaying ads within boards
	 *
	 * Отображение рекламы в разделах
	 *
	 * @return void
	 */
	public static function messageindexButtons()
	{
		global $context;

		require_once(__DIR__ . '/Template.php');
		$context['template_layers'][] = 'ads_placement_board';
	}

	/**
	 * Displaying ads within topics
	 *
	 * Отображение рекламы в темах
	 *
	 * @return void
	 */
	public static function displayButtons()
	{
		global $context;

		require_once(__DIR__ . '/Template.php');
		$context['template_layers'][] = 'ads_placement_topic';
	}

	/**
	 * Displaying ads within posts
	 *
	 * Отображение рекламы в сообщениях
	 *
	 * @param array $output
	 * @param array $message
	 * @param int $counter
	 * @return void
	 */
	public static function prepareDisplayContext(&$output, &$message, $counter)
	{
		global $context;

		/**
		 * Displaying ads before the first message
		 *
		 * Вывод рекламы перед первым сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_first_post']) && $output['id'] == $context['topic_first_message']) {
			lp_show_blocks('before_first_post');
		}

		/**
		 * Displaying ads before each first message on the page
		 *
		 * Вывод рекламы перед каждым первым сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_every_first_post']) && $output['counter'] == $context['start']) {
			lp_show_blocks('before_every_first_post');
		}

		/**
		 * Displaying ads before each last message on the page
		 *
		 * Вывод рекламы перед каждым последним сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_every_last_post']) && ($counter == $context['total_visible_posts'] || $counter % $context['messages_per_page'] == 0)) {
			lp_show_blocks('before_every_last_post');
		}

		/**
		 * Displaying ads before the last message
		 *
		 * Вывод рекламы перед последним сообщением
		 */
		if (!empty($context['lp_ads_blocks']['before_last_post']) && $output['id'] == $context['topic_last_message']) {
			lp_show_blocks('before_last_post');
		}

		/**
		 * Displaying ads after the first message
		 *
		 * Вывод рекламы после первого сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_first_post']) && $counter == 2) {
			lp_show_blocks('after_first_post');
		}

		/**
		 * Displaying ads after each first message on the page
		 *
		 * Вывод рекламы после каждого первого сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_every_first_post']) && ($output['counter'] == $context['start'] + 1)) {
			lp_show_blocks('after_every_first_post');
		}

		/**
		 * Displaying ads after every fifth message on the page
		 *
		 * Вывод рекламы после каждого пятого сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_every_five_post']) && $counter % 6 == 0) {
			lp_show_blocks('after_every_five_post');
		}

		/**
		 * Displaying ads after each last message on the page
		 *
		 * Вывод рекламы после каждого последнего сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_every_last_post']) && ($counter == $context['total_visible_posts'] || $counter % $context['messages_per_page'] == 0)) {
			ob_start();

			lp_show_blocks('after_every_last_post');

			$after_every_last_post = ob_get_clean();

			addInlineJavaScript('
		const all_windowbg = document.getElementById("quickModForm").querySelectorAll("div.windowbg");
		all_windowbg[all_windowbg.length - 1].insertAdjacentHTML("afterend", ' . JavaScriptEscape($after_every_last_post) . ');', true);
		}

		/**
		 * Displaying ads after the last message
		 *
		 * Вывод рекламы после последнего сообщения
		 */
		if (!empty($context['lp_ads_blocks']['after_last_post']) && $output['id'] == $context['topic_last_message']) {
			ob_start();

			lp_show_blocks('after_last_post');

			$after_last_post = ob_get_clean();

			addInlineJavaScript('
		document.getElementById("quickModForm").insertAdjacentHTML("beforeend", ' . JavaScriptEscape($after_last_post) . ');', true);
		}
	}

	/**
	 * Get ads blocks by selected position
	 *
	 * Получаем рекламные блоки в указанной позиции
	 *
	 * @param string $position
	 * @return array
	 */
	private static function getByPosition($position)
	{
		global $context;

		if (empty($position))
			return [];

		$blocks = array_filter($context['lp_blocks']['ads'], function ($block) use ($position, $context) {
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

		return $blocks;
	}

	/**
	 * Add advertising areas to panel settings
	 *
	 * Добавляем рекламные области в настройки панелей
	 *
	 * @return void
	 */
	public static function addPanels()
	{
		global $context, $txt;

		unset($context['lp_panels']['ads']);
		$context['lp_panels'] += $txt['lp_ads_block_addon_placement_set'];
	}

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['ads_block']['content'] = 'html';

		$options['ads_block']['parameters']['ads_placement'] = static::$placement;
		$options['ads_block']['parameters']['ads_boards']    = static::$boards;
		$options['ads_block']['parameters']['ads_topics']    = static::$topics;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public static function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'ads_block')
			return;

		$parameters['ads_placement'] = array(
			'name'   => 'ads_placement',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
		$parameters['ads_boards'] = FILTER_SANITIZE_STRING;
		$parameters['ads_topics'] = FILTER_SANITIZE_STRING;
	}

	/**
	 * Override some standard fields
	 *
	 * Переопределяем некоторые стандартные поля
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'ads_block')
			return;

		$context['posting_fields']['placement']['label']['text'] = '';
		$context['posting_fields']['placement']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => 'ads',
				'required'  => true,
				'style'     => 'display: none'
			)
		);

		$context['posting_fields']['areas']['label']['text'] = '';
		$context['posting_fields']['areas']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => 'all',
				'required'  => true,
				'style'     => 'display: none'
			)
		);

		$context['lp_block']['options']['parameters']['ads_placement'] = is_array($context['lp_block']['options']['parameters']['ads_placement']) ? $context['lp_block']['options']['parameters']['ads_placement'] : explode(',', $context['lp_block']['options']['parameters']['ads_placement']);

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

		foreach ($txt['lp_ads_block_addon_placement_set'] as $position => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['ads_placement']['input']['options'][$title]['attributes'] = array(
					'value'    => $position,
					'selected' => in_array($position, $context['lp_block']['options']['parameters']['ads_placement'])
				);
			} else {
				$context['posting_fields']['ads_placement']['input']['options'][$title] = array(
					'value'    => $position,
					'selected' => in_array($position, $context['lp_block']['options']['parameters']['ads_placement'])
				);
			}
		}

		$context['posting_fields']['ads_boards']['label']['text'] = $txt['lp_ads_block_addon_ads_boards'];
		$context['posting_fields']['ads_boards']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_ads_block_addon_ads_boards_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['ads_boards'] ?? '',
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['ads_topics']['label']['text'] = $txt['lp_ads_block_addon_ads_topics'];
		$context['posting_fields']['ads_topics']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_ads_block_addon_ads_topics_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['ads_topics'] ?? '',
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);
	}
}

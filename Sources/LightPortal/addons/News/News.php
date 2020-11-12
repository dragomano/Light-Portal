<?php

namespace Bugo\LightPortal\Addons\News;

/**
 * News
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class News
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'far fa-newspaper';

	/**
	 * The news index to be displayed (0 - random news)
	 *
	 * Порядковый номер новости для отображения (0 - случайная новость)
	 *
	 * @var int
	 */
	private static $selected_item = 0;

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
		$options['news']['parameters']['selected_item'] = static::$selected_item;
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
		if ($type !== 'news')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'news')
			return;

		$context['posting_fields']['selected_item']['label']['text'] = $txt['lp_news_addon_selected_item'];
		$context['posting_fields']['selected_item']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'selected_item'
			),
			'options' => array(),
			'tab' => 'content'
		);

		self::getData();

		$news = [$txt['lp_news_addon_random_news']];
		if (!empty($context['news_lines'])) {
			array_unshift($context['news_lines'], $txt['lp_news_addon_random_news']);
			$news = $context['news_lines'];
		}

		foreach ($news as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['selected_item']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
				);
			} else {
				$context['posting_fields']['selected_item']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
				);
			}
		}
	}

	/**
	 * Get the forum news
	 *
	 * Получаем новость форума
	 *
	 * @param int $item
	 * @return string
	 */
	public static function getData($item = 0)
	{
		global $boarddir, $context;

		require_once($boarddir . '/SSI.php');
		setupThemeContext();

		if ($item > 0)
			return $context['news_lines'][$item - 1];

		return ssi_news('return');
	}

	/**
	 * Form the content block
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $txt;

		if ($type !== 'news')
			return;

		$news = self::getData($parameters['selected_item']);

		ob_start();

		echo $news ?: $txt['lp_news_addon_no_items'];

		$content = ob_get_clean();
	}
}

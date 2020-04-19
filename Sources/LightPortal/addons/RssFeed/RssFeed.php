<?php

namespace Bugo\LightPortal\Addons\RssFeed;

use Bugo\LightPortal\Helpers;

/**
 * RssFeed
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RssFeed
{
	/**
	 * RSS Feed Url
	 *
	 * Адрес ленты RSS
	 *
	 * @var string
	 */
	private static $url = '';

	/**
	 * Show text (true|false)
	 *
	 * Отображать текст (true|false)
	 *
	 * @var bool
	 */
	private static $show_text = false;

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
		$options['rss_feed'] = array(
			'parameters' => array(
				'url'       => static::$url,
				'show_text' => static::$show_text
			)
		);
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'rss_feed')
			return;

		$args['parameters'] = array(
			'url'       => FILTER_VALIDATE_URL,
			'show_text' => FILTER_VALIDATE_BOOLEAN
		);
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
		global $context, $txt, $scripturl;

		if ($context['lp_block']['type'] !== 'rss_feed')
			return;

		$context['posting_fields']['url']['label']['text'] = $txt['lp_rss_feed_addon_url'];
		$context['posting_fields']['url']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength'   => 255,
				'value'       => $context['lp_block']['options']['parameters']['url'],
				'placeholder' => $scripturl . '?action=.xml;type=rss2',
				'required'    => true,
				'style'       => 'width: 100%'
			)
		);

		$context['posting_fields']['show_text']['label']['text'] = $txt['lp_rss_feed_addon_show_text'];
		$context['posting_fields']['show_text']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_text',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_text'])
			)
		);
	}

	/**
	 * Get the file content by the specified $url
	 *
	 * Получаем содержимое файла по указанному $url
	 *
	 * @param string $url
	 * @return void
	 */
	public static function getRssFromUrl($url)
	{
		return file_get_contents($url);
	}

	/**
	 * Get the SimpleXML object
	 *
	 * Получаем объект SimpleXML
	 *
	 * @param string $file
	 * @return mixed
	 */
	private static function getRssItems($file)
	{
		$rss = simplexml_load_string($file);

		return $rss ? $rss->channel->item : null;
	}

	/**
	 * Form the block content
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
		if ($type !== 'rss_feed')
			return;

		$rss_file = Helpers::getFromCache('rss_feed_addon_b' . $block_id, 'getRssFromUrl', __CLASS__, $cache_time, $parameters['url']);
		$rss_feed = self::getRssItems($rss_file);

		if (!empty($rss_feed)) {
			ob_start();

			foreach ($rss_feed as $item) {
				echo '
			<div class="windowbg">
				<div class="block">
					<span class="floatleft half_content">
						<h5><a href="', $item->link, '">', $item->title, '</a></h5>
						<em>', Helpers::getFriendlyTime(strtotime($item->pubDate)), '</em>
					</span>
				</div>';

				if ($parameters['show_text']) {
					echo '
				<div class="list_posts double_height">
					', $item->description, '
				</div>';
				}

				echo '
			</div>';
			}

			$content = ob_get_clean();
		}
	}
}

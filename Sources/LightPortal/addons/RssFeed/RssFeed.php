<?php

namespace Bugo\LightPortal\Addons\RssFeed;

use Bugo\LightPortal\Helpers;

/**
 * RssFeed
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RssFeed
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-rss';

	/**
	 * @var string
	 */
	private $url = '';

	/**
	 * @var bool
	 */
	private $show_text = false;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['rss_feed']['parameters']['url']       = $this->url;
		$options['rss_feed']['parameters']['show_text'] = $this->show_text;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'rss_feed')
			return;

		$parameters['url']       = FILTER_VALIDATE_URL;
		$parameters['show_text'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt, $scripturl;

		if ($context['lp_block']['type'] !== 'rss_feed')
			return;

		$context['posting_fields']['url']['label']['text'] = $txt['lp_rss_feed_addon_url'];
		$context['posting_fields']['url']['input'] = array(
			'type' => 'url',
			'attributes' => array(
				'maxlength'   => 255,
				'value'       => $context['lp_block']['options']['parameters']['url'],
				'placeholder' => $scripturl . '?action=.xml;type=rss2',
				'required'    => true,
				'style'       => 'width: 100%'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_text']['label']['text'] = $txt['lp_rss_feed_addon_show_text'];
		$context['posting_fields']['show_text']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_text',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_text'])
			),
			'tab' => 'content'
		);
	}

	/**
	 * Get the SimpleXML object
	 *
	 * Получаем объект SimpleXML
	 *
	 * @param string $url
	 * @return mixed
	 */
	public function getData($url)
	{
		if (empty($url))
			return '';

		$file = file_get_contents($url);
		$rss  = simplexml_load_string($file);

		return $rss ? $rss->channel->item : null;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		if ($type !== 'rss_feed')
			return;

		$rss_feed = Helpers::cache('rss_feed_addon_b' . $block_id, 'getData', __CLASS__, $cache_time, $parameters['url']);

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
				' . $item->description . '
			</div>';
				}

				echo '
		</div>';
			}

			$content = ob_get_clean();
		}
	}
}

<?php

namespace Bugo\LightPortal\Addons\News;

/**
 * News
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class News
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-newspaper';

	/**
	 * @var int
	 */
	private $selected_item = 0;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['news']['parameters']['selected_item'] = $this->selected_item;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'news')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
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

		$this->getData();

		$news = [$txt['lp_news_addon_random_news']];
		if (!empty($context['news_lines'])) {
			array_unshift($context['news_lines'], $txt['lp_news_addon_random_news']);
			$news = $context['news_lines'];
		}

		foreach ($news as $key => $value) {
			$context['posting_fields']['selected_item']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
			);
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
	public function getData($item = 0)
	{
		global $boarddir, $context;

		require_once($boarddir . '/SSI.php');
		setupThemeContext();

		if ($item > 0)
			return $context['news_lines'][$item - 1];

		return ssi_news('return');
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
		global $txt;

		if ($type !== 'news')
			return;

		$news = $this->getData($parameters['selected_item']);

		ob_start();

		echo $news ?: $txt['lp_news_addon_no_items'];

		$content = ob_get_clean();
	}
}

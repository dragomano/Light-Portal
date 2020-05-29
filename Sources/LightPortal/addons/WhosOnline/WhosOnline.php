<?php

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Helpers;

/**
 * WhosOnline
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

class WhosOnline
{
	/**
	 * Online list update interval, in seconds
	 *
	 * Интервал обновления списка онлайн, в секундах
	 *
	 * @var int
	 */
	private static $update_interval = 600;

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
		$options['whos_online'] = array(
			'parameters' => array(
				'update_interval' => static::$update_interval
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

		if ($context['current_block']['type'] !== 'whos_online')
			return;

		$args['parameters'] = array(
			'update_interval' => FILTER_VALIDATE_INT
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
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'whos_online')
			return;

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_whos_online_addon_update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'update_interval',
				'min' => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	/**
	 * Get the list of online members
	 *
	 * Получаем список пользователей онлайн
	 *
	 * @return array
	 */
	private static function getData()
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_whosOnline('array');
	}

	/**
	 * Get the block html code
	 *
	 * Получаем html-код блока
	 *
	 * @return string
	 */
	public static function getHtml()
	{
		global $user_info, $txt, $settings;

		$whos_online = self::getData();

		$html = Helpers::getCorrectDeclension(comma_format($whos_online['num_guests']), $txt['lp_guests_set']) . ', ' . Helpers::getCorrectDeclension(comma_format($whos_online['num_users_online']), $txt['lp_users_set']);

		$online_list = [];
		if (!empty($user_info['buddies']))
			$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_buddies']), $txt['lp_buddies_set']);
		if (!empty($whos_online['num_spiders']))
			$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_spiders']), $txt['lp_spiders_set']);
		if (!empty($whos_online['num_users_hidden']))
			$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_users_hidden']), $txt['lp_hidden_set']);

		if (!empty($online_list))
			$html .= ' (' . implode(', ', $online_list) . ')';

		$html .= '
		<br>' . implode(', ', $whos_online['list_users_online']);

		if (!empty($settings['show_group_key']) && !empty($whos_online['membergroups']))
			$html .= '
		<br>[' . implode(']&nbsp;&nbsp;[', $whos_online['membergroups']) . ']';

		return $html;
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
		global $user_info;

		if ($type !== 'whos_online')
			return;

		$whos_online = Helpers::getFromCache('whos_online_addon_b' . $block_id . '_u' . $user_info['id'], 'getHtml', __CLASS__, $parameters['update_interval'] ?? $cache_time);

		if (!empty($whos_online)) {
			ob_start();
			echo $whos_online;
			$content = ob_get_clean();
		}
	}
}

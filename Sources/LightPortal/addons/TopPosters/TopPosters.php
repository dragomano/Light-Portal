<?php

namespace Bugo\LightPortal\Addons\TopPosters;

use Bugo\LightPortal\Helpers;

/**
 * TopPosters
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

class TopPosters
{
	/**
	 * Display user avatars (true|false)
	 *
	 * Отображать аватарки пользователей (true|false)
	 *
	 * @var bool
	 */
	private static $show_avatars = true;

	/**
	 * The maximum number of users to output
	 *
	 * Максимальное количество пользователей для вывода
	 *
	 * @var int
	 */
	private static $num_posters = 10;

	/**
	 * Display only numbers (true|false)
	 *
	 * Отображать только цифры (true|false)
	 *
	 * @var bool
	 */
	private static $show_numbers_only = false;

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
		$options['top_posters'] = array(
			'parameters' => array(
				'show_avatars'      => static::$show_avatars,
				'num_posters'       => static::$num_posters,
				'show_numbers_only' => static::$show_numbers_only
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

		if ($context['current_block']['type'] !== 'top_posters')
			return;

		$args['parameters'] = array(
			'show_avatars'      => FILTER_VALIDATE_BOOLEAN,
			'num_posters'       => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN
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

		if ($context['lp_block']['type'] !== 'top_posters')
			return;

		$context['posting_fields']['show_avatars']['label']['text'] = $txt['lp_top_posters_addon_show_avatars'];
		$context['posting_fields']['show_avatars']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_avatars',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_avatars'])
			)
		);

		$context['posting_fields']['num_posters']['label']['text'] = $txt['lp_top_posters_addon_num_posters'];
		$context['posting_fields']['num_posters']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_posters',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posters']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_posters_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of top members
	 *
	 * Получаем список лучших пользователей
	 *
	 * @param array $parameters
	 * @return array
	 */
	private static function getData($parameters)
	{
		global $smcFunc, $scripturl, $modSettings, $context;

		extract($parameters);

		$request = $smcFunc['db_query']('', '
			SELECT mem.id_member, mem.real_name, mem.posts' . ($show_avatars ? ', mem.avatar, a.id_attach, a.attachment_type, a.filename' : '') . '
			FROM {db_prefix}members AS mem' . ($show_avatars ? '
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)' : '') . '
			WHERE mem.posts > {int:num_posts}
			ORDER BY mem.posts DESC
			LIMIT {int:num_posters}',
			array(
				'num_posts'   => 0,
				'num_posters' => $num_posters
			)
		);

		$posters = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))	{
			$posters[] = array(
				'link'   => allowedTo('profile_view') ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['real_name'],
				'avatar' => $show_avatars ? ($row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : $modSettings['avatar_url'] . '/default.png') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])) : null,
				'posts'  => $row['posts']
			);
		}

		$smcFunc['db_free_result']($request);

		$context++;

		return $posters;
	}

	/**
	 * Get the block html code
	 *
	 * Получаем html-код блока
	 *
	 * @param array $parameters
	 * @return string
	 */
	public static function getHtml($parameters)
	{
		global $txt;

		$top_posters = self::getData($parameters);

		if (empty($top_posters))
			return '';

		$html = '
		<dl class="top_posters stats">';

		$max = $top_posters[0]['posts'];

		foreach ($top_posters as $poster) {
			$html .= '
			<dt>';

			if (!empty($poster['avatar'])) {
				$html .= '
				<img src="' . $poster['avatar'] . '" alt="*"> ';
			}

			$width = $poster['posts'] * 100 / $max;

			$html .= '
				' . $poster['link'] . '
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar' . (empty($poster['posts']) ? ' empty"' : '" style="width: ' . $width . '%"') . '></div>
				<span>' . ($parameters['show_numbers_only'] ? $poster['posts'] : Helpers::getCorrectDeclension($poster['posts'], $txt['lp_posts_set'])) . '</span>
			</dd>';
		}

		$html .= '
		</dl>';

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

		if ($type !== 'top_posters')
			return;

		$top_posters = Helpers::getFromCache('top_posters_addon_b' . $block_id . '_u' . $user_info['id'], 'getHtml', __CLASS__, $cache_time, $parameters);

		if (!empty($top_posters)) {
			ob_start();
			echo $top_posters;
			$content = ob_get_clean();
		}
	}
}

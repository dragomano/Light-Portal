<?php

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\LightPortal\Helpers;

/**
 * TopBoards
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopBoards
{
	/**
	 * Максимальное количество разделов для вывода
	 *
	 * @var int
	 */
	private static $num_boards = 10;

	/**
	 * Отображать только цифры, или нет
	 *
	 * @var bool
	 */
	private static $show_numbers_only = false;

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['top_boards'] = array(
			'parameters' => array(
				'num_boards'        => static::$num_boards,
				'show_numbers_only' => static::$show_numbers_only
			)
		);
	}

	/**
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'top_boards')
			return;

		$args['parameters'] = array(
			'num_boards'        => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_boards')
			return;

		$context['posting_fields']['num_boards']['label']['text'] = $txt['lp_top_boards_addon_num_boards'];
		$context['posting_fields']['num_boards']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_boards',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_boards']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_posters_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Получаем список популярных разделов
	 *
	 * @param int $num_boards
	 * @return void
	 */
	public static function getTopBoards($num_boards)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_topBoards($num_boards, 'array');
	}

	/**
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
		global $context, $txt;

		if ($type !== 'top_boards')
			return;

		$top_boards = Helpers::useCache('top_boards_addon_b' . $block_id . '_u' . $context['user']['id'], 'getTopBoards', __CLASS__, $cache_time, $parameters['num_boards']);

		if (!empty($top_boards)) {
			ob_start();

			echo '
			<dl class="stats">';

			$max = $top_boards[0]['num_topics'];

			foreach ($top_boards as $board) {
				if ($board['num_topics'] < 1)
					continue;

				$width = $board['num_topics'] * 100 / $max;

				echo '
				<dt>', $board['link'], '</dt>
				<dd class="statsbar generic_bar righttext">
					<div class="bar', (empty($board['num_topics']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
					<span>', $parameters['show_numbers_only'] ? $board['num_topics'] : Helpers::getCorrectDeclension($board['num_topics'], $txt['lp_top_boards_addon_topics']), '</span>
				</dd>';
			}

			echo '
			</dl>';

			$content = ob_get_clean();
		}
	}
}

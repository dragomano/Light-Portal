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
 * @version 0.5
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
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['top_boards'] = array(
			'parameters' => array(
				'num_boards' => static::$num_boards
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
			'num_boards' => FILTER_VALIDATE_INT
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
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $context, $boarddir, $txt;

		if ($type !== 'top_boards')
			return;

		$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? $context['lp_block']['options']['parameters'];

		if (($top_boards = cache_get_data('light_portal_top_boards_addon', 3600)) == null) {
			require_once($boarddir . '/SSI.php');
			$top_boards = ssi_topBoards($parameters['num_boards'], 'array');
			cache_put_data('light_portal_top_boards_addon', $top_boards, 3600);
		}

		ob_start();

		if (!empty($top_boards)) {
			echo '
			<dl class="stats">';

			$max = $top_boards[0]['num_topics'];

			foreach ($top_boards as $board) {
				$width = $board['num_topics'] * 100 / $max;

				echo '
				<dt>', $board['link'], '</dt>
				<dd class="statsbar generic_bar righttext">
					<div class="bar', (empty($board['num_topics']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
					<span>', Helpers::correctDeclension($board['num_topics'], $txt['lp_top_boards_addon_topics']), '</span>
				</dd>';
			}

			echo '
			</dl>';
		}

		$content = ob_get_clean();
	}
}

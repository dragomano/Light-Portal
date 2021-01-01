<?php

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\LightPortal\Helpers;

/**
 * TopBoards
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopBoards
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-balance-scale-left';

	/**
	 * @var int
	 */
	private $num_boards = 10;

	/**
	 * @var bool
	 */
	private $show_numbers_only = false;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['top_boards']['parameters']['num_boards']        = $this->num_boards;
		$options['top_boards']['parameters']['show_numbers_only'] = $this->show_numbers_only;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'top_boards')
			return;

		$parameters['num_boards']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_boards')
			return;

		$context['posting_fields']['num_boards']['label']['text'] = $txt['lp_top_boards_addon_num_boards'];
		$context['posting_fields']['num_boards']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_boards',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_boards']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_boards_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular boards
	 *
	 * Получаем список популярных разделов
	 *
	 * @param int $num_boards
	 * @return array
	 */
	public function getData($num_boards)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');

		return ssi_topBoards($num_boards, 'array');
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
		global $user_info, $txt;

		if ($type !== 'top_boards')
			return;

		$top_boards = Helpers::cache('top_boards_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $cache_time, $parameters['num_boards']);

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
				<span>', ($parameters['show_numbers_only'] ? $board['num_topics'] : Helpers::getCorrectDeclension($board['num_topics'], $txt['lp_top_boards_addon_topics'])), '</span>
			</dd>';
			}

			echo '
		</dl>';

			$content = ob_get_clean();
		}
	}
}

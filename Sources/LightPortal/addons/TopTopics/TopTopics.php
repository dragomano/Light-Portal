<?php

namespace Bugo\LightPortal\Addons\TopTopics;

use Bugo\LightPortal\Helpers;

/**
 * TopTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopTopics
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-balance-scale-left';

	/**
	 * @var string
	 */
	private $type = 'replies';

	/**
	 * @var int
	 */
	private $num_topics = 10;

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
		$options['top_topics']['parameters']['popularity_type']   = $this->type;
		$options['top_topics']['parameters']['num_topics']        = $this->num_topics;
		$options['top_topics']['parameters']['show_numbers_only'] = $this->show_numbers_only;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'top_topics')
			return;

		$parameters['popularity_type']   = FILTER_SANITIZE_STRING;
		$parameters['num_topics']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_topics')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_topics_addon_type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		foreach ($txt['lp_top_topics_addon_type_set'] as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['popularity_type']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
				);
			} else {
				$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
				);
			}
		}

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_top_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_topics_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular topics
	 *
	 * Получаем список популярных тем
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData($parameters)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_topTopics($parameters['popularity_type'], $parameters['num_topics'], 'array');
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

		if ($type !== 'top_topics')
			return;

		$top_topics = Helpers::cache('top_topics_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $cache_time, $parameters);

		if (!empty($top_topics)) {
			ob_start();

			echo '
		<dl class="stats">';

			$max = $top_topics[0]['num_' . $parameters['popularity_type']];

			foreach ($top_topics as $topic) {
				if ($topic['num_' . $parameters['popularity_type']] < 1)
					continue;

				$width = $topic['num_' . $parameters['popularity_type']] * 100 / $max;

				echo '
			<dt>', $topic['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($topic['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : Helpers::getCorrectDeclension($topic['num_' . $parameters['popularity_type']], $txt['lp_' . $parameters['popularity_type'] . '_set'])), '</span>
			</dd>';
			}

			echo '
		</dl>';

			$content = ob_get_clean();
		}
	}
}

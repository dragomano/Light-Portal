<?php

namespace Bugo\LightPortal\Addons\RecentAttachments;

use Bugo\LightPortal\Helpers;

/**
 * RecentAttachments
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

class RecentAttachments
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-paperclip';

	/**
	 * @var int
	 */
	private $num_attachments = 5;

	/**
	 * @var string
	 */
	private $extensions = '';

	/**
	 * @var string
	 */
	private $direction = 'horizontal';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['recent_attachments']['parameters']['num_attachments'] = $this->num_attachments;
		$options['recent_attachments']['parameters']['extensions']      = $this->extensions;
		$options['recent_attachments']['parameters']['direction']       = $this->direction;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'recent_attachments')
			return;

		$parameters['num_attachments'] = FILTER_VALIDATE_INT;
		$parameters['extensions']      = FILTER_SANITIZE_STRING;
		$parameters['direction']       = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_attachments')
			return;

		$context['posting_fields']['num_attachments']['label']['text'] = $txt['lp_recent_attachments_addon_num_attachments'];
		$context['posting_fields']['num_attachments']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_attachments',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_attachments']
			)
		);

		$context['posting_fields']['extensions']['label']['text']  = $txt['lp_recent_attachments_addon_extensions'];
		$context['posting_fields']['extensions']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_recent_attachments_addon_extensions_subtext'],
			'attributes' => array(
				'id'        => 'extensions',
				'maxlength' => 30,
				'value'     => $context['lp_block']['options']['parameters']['extensions'],
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['direction']['label']['text'] = $txt['lp_recent_attachments_addon_direction'];
		$context['posting_fields']['direction']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'direction'
			),
			'options' => array()
		);

		$directions = array_combine(array('horizontal', 'vertical'), $txt['lp_panel_direction_set']);

		foreach ($directions as $direction => $title) {
			$context['posting_fields']['direction']['input']['options'][$title] = array(
				'value'    => $direction,
				'selected' => $direction == $context['lp_block']['options']['parameters']['direction']
			);
		}
	}

	/**
	 * Get the list of recent attachments
	 *
	 * Получаем список последних вложений
	 *
	 * @param array $parameters
	 * @return string
	 */
	public function getData(array $parameters)
	{
		global $boarddir;

		$extensions = !empty($parameters['extensions']) ? explode(',', $parameters['extensions']) : [];

		require_once($boarddir . '/SSI.php');
		return ssi_recentAttachments($parameters['num_attachments'], $extensions, 'array');
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
		global $user_info, $settings;

		if ($type !== 'recent_attachments')
			return;

		$attachment_list = Helpers::cache(
			'recent_attachments_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$cache_time,
			$parameters
		);

		if (!empty($attachment_list)) {
			ob_start();

			$fancybox = class_exists('FancyBox');

			echo '
		<div class="recent_attachments' . ($parameters['direction'] == 'vertical' ? ' column_direction' : '') . '">';

			foreach ($attachment_list as $attach) {
				if (!empty($attach['file']['image'])) {
					echo '
			<div class="item">
				<a', ($fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $block_id . '"' : ''), ' href="', $attach['file']['href'], ';image">', $attach['file']['image']['thumb'], '</a>
			</div>';
				} else {
					echo '
			<div class="item">
				<a href="', $attach['file']['href'], '">
					<img class="centericon" src="', $settings['images_url'], '/icons/clip.png" alt="', $attach['file']['filename'], '"> ', $attach['file']['filename'], '
				</a> (', $attach['file']['filesize'], ')
			</div>';
				}
			}

			echo '
		</div>';

			$content = ob_get_clean();
		}
	}
}

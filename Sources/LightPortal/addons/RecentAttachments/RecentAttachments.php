<?php

namespace Bugo\LightPortal\Addons\RecentAttachments;

use Bugo\LightPortal\Helpers;

/**
 * RecentAttachments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.8.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentAttachments
{
	/**
	 * Максимальное количество вложений для вывода
	 *
	 * @var int
	 */
	private static $num_attachments = 5;

	/**
	 * Расширения вложений, которые нужно отображать
	 *
	 * @var string
	 */
	private static $extensions = '';

	/**
	 * Направление списка вложений (horizontal|vertical)
	 *
	 * @var string
	 */
	private static $direction = 'horizontal';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['recent_attachments'] = array(
			'parameters' => array(
				'num_attachments' => static::$num_attachments,
				'extensions'      => static::$extensions,
				'direction'       => static::$direction
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

		if ($context['current_block']['type'] !== 'recent_attachments')
			return;

		$args['parameters'] = array(
			'num_attachments' => FILTER_VALIDATE_INT,
			'extensions'      => FILTER_SANITIZE_STRING,
			'direction'       => FILTER_SANITIZE_STRING
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

		foreach ($txt['lp_recent_attachments_addon_direction_set'] as $direction => $title) {
			$context['posting_fields']['direction']['input']['options'][$title] = array(
				'value'    => $direction,
				'selected' => $direction == $context['lp_block']['options']['parameters']['direction']
			);
		}
	}

	/**
	 * Получаем список последних вложений
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getRecentAttachments($params)
	{
		global $boarddir;

		[$num_attachments, $extensions] = $params;
		$extensions = !empty($extensions) ? explode(',', $extensions) : [];

		require_once($boarddir . '/SSI.php');
		return ssi_recentAttachments($num_attachments, $extensions, 'array');
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
		global $context, $settings;

		if ($type !== 'recent_attachments')
			return;

		$recent_attachments = Helpers::useCache(
			'recent_attachments_addon_b' . $block_id . '_u' . $context['user']['id'],
			'getRecentAttachments',
			__CLASS__,
			$cache_time,
			array($parameters['num_attachments'], $parameters['extensions'])
		);

		if (!empty($recent_attachments)) {
			$fancybox = class_exists('FancyBox');

			ob_start();

			echo '
			<div class="recent_attachments', $parameters['direction'] == 'vertical' ? ' column_direction' : '', '">';

			foreach ($recent_attachments as $attach) {
				if (!empty($attach['file']['image'])) {
					echo '
				<div class="item">
					<a', $fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $block_id . '"' : '', ' href="', $attach['file']['href'], ';image">', $attach['file']['image']['thumb'], '</a>
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

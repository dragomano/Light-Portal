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
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentAttachments
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-paperclip';

	/**
	 * The maximum number of attachments to output
	 *
	 * Максимальное количество вложений для вывода
	 *
	 * @var int
	 */
	private static $num_attachments = 5;

	/**
	 * Attachment extensions to display
	 *
	 * Расширения вложений, которые нужно отображать
	 *
	 * @var string
	 */
	private static $extensions = '';

	/**
	 * The attachment list direction (horizontal|vertical)
	 *
	 * Направление списка вложений (horizontal|vertical)
	 *
	 * @var string
	 */
	private static $direction = 'horizontal';

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
		$options['recent_attachments'] = array(
			'parameters' => array(
				'num_attachments' => static::$num_attachments,
				'extensions'      => static::$extensions,
				'direction'       => static::$direction
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

		if ($context['current_block']['type'] !== 'recent_attachments')
			return;

		$args['parameters'] = array(
			'num_attachments' => FILTER_VALIDATE_INT,
			'extensions'      => FILTER_SANITIZE_STRING,
			'direction'       => FILTER_SANITIZE_STRING
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
			if (!defined('JQUERY_VERSION')) {
				$context['posting_fields']['direction']['input']['options'][$title]['attributes'] = array(
					'value'    => $direction,
					'selected' => $direction == $context['lp_block']['options']['parameters']['direction']
				);
			} else {
				$context['posting_fields']['direction']['input']['options'][$title] = array(
					'value'    => $direction,
					'selected' => $direction == $context['lp_block']['options']['parameters']['direction']
				);
			}
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
	private static function getData($parameters)
	{
		global $boarddir;

		extract($parameters);
		$extensions = !empty($extensions) ? explode(',', $extensions) : [];

		require_once($boarddir . '/SSI.php');
		return ssi_recentAttachments($num_attachments, $extensions, 'array');
	}

	/**
	 * Get the block html code
	 *
	 * Получаем html-код блока
	 *
	 * @param int $block_id
	 * @param array $parameters
	 * @return string
	 */
	public static function getHtml($block_id, $parameters)
	{
		global $settings;

		$recent_attachments = self::getData($parameters);

		$html = '';
		if (!empty($recent_attachments)) {
			$fancybox = class_exists('FancyBox');

			$html .= '
		<div class="recent_attachments' . ($parameters['direction'] == 'vertical' ? ' column_direction' : '') . '">';

			foreach ($recent_attachments as $attach) {
				if (!empty($attach['file']['image'])) {
					$html .= '
			<div class="item">
				<a' . ($fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $block_id . '"' : '') . ' href="' . $attach['file']['href'] . ';image">' . $attach['file']['image']['thumb'] . '</a>
			</div>';
				} else {
					$html .= '
			<div class="item">
				<a href="' . $attach['file']['href'] . '">
					<img class="centericon" src="' . $settings['images_url'] . '/icons/clip.png" alt="' . $attach['file']['filename'] . '"> ' . $attach['file']['filename'] . '
				</a> (' . $attach['file']['filesize'] . ')
			</div>';
				}
			}

			$html .= '
		</div>';
		}

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

		if ($type !== 'recent_attachments')
			return;

		$attachment_list = Helpers::getFromCache('recent_attachments_addon_b' . $block_id . '_u' . $user_info['id'], 'getHtml', __CLASS__, $cache_time, $block_id, $parameters);

		if (!empty($attachment_list)) {
			ob_start();
			echo $attachment_list;
			$content = ob_get_clean();
		}
	}
}

<?php

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Helpers;
use Bugo\LightPortal\Subs;

/**
 * ArticleList
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

class ArticleList
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'far fa-file-alt';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Default class for article blocks
	 *
	 * Класс (по умолчанию) для оформления блоков статей
	 *
	 * @var string
	 */
	private static $article_body_class = 'div.descbox';

	/**
	 * Articles type (0 - topics, 1 - pages)
	 *
	 * Тип статей (0 - темы, 1 - страницы)
	 *
	 * @var int
	 */
	private static $article_type = 0;

	/**
	 * IDs of topics or pages to display
	 *
	 * Идентификаторы тем или страниц для отображения
	 *
	 * @var string
	 */
	private static $ids = '';

	/**
	 * The article list direction (horizontal|vertical)
	 *
	 * Направление списка статей (horizontal|vertical)
	 *
	 * @var string
	 */
	private static $direction = 'horizontal';

	/**
	 * Display article images (true|false)
	 *
	 * Отображать картинки статей (true|false)
	 *
	 * @var bool
	 */
	private static $show_images = false;

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
		$options['article_list'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'article_body_class' => static::$article_body_class,
				'article_type'       => static::$article_type,
				'ids'                => static::$ids,
				'direction'          => static::$direction,
				'show_images'        => static::$show_images
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

		if ($context['current_block']['type'] !== 'article_list')
			return;

		$args['parameters'] = array(
			'article_body_class' => FILTER_SANITIZE_STRING,
			'article_type'       => FILTER_VALIDATE_INT,
			'ids'                => FILTER_SANITIZE_STRING,
			'direction'          => FILTER_SANITIZE_STRING,
			'show_images'        => FILTER_VALIDATE_BOOLEAN
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

		if ($context['lp_block']['type'] !== 'article_list')
			return;

		$context['posting_fields']['article_body_class']['label']['text'] = $txt['lp_article_list_addon_body_class'];
		$context['posting_fields']['article_body_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'article_body_class'
			),
			'options' => array()
		);

		foreach ($context['lp_all_content_classes'] as $key => $data) {
			$value = $key;
			$key   = $key == '_' ? $txt['no'] : $key;

			if (RC2_CLEAN) {
				$context['posting_fields']['article_body_class']['input']['options'][$key]['attributes'] = array(
					'value'    => $value,
					'selected' => $value == $context['lp_block']['options']['parameters']['article_body_class']
				);
			} else {
				$context['posting_fields']['article_body_class']['input']['options'][$key] = array(
					'value'    => $value,
					'selected' => $value == $context['lp_block']['options']['parameters']['article_body_class']
				);
			}
		}

		$context['posting_fields']['article_type']['label']['text'] = $txt['lp_article_list_addon_article_type'];
		$context['posting_fields']['article_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'article_type'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_article_list_addon_article_type_set'] as $article_type => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['article_type']['input']['options'][$title]['attributes'] = array(
					'value'    => $article_type,
					'selected' => $article_type == $context['lp_block']['options']['parameters']['article_type']
				);
			} else {
				$context['posting_fields']['article_type']['input']['options'][$title] = array(
					'value'    => $article_type,
					'selected' => $article_type == $context['lp_block']['options']['parameters']['article_type']
				);
			}
		}

		$context['posting_fields']['ids']['label']['text'] = $txt['lp_article_list_addon_ids'];
		$context['posting_fields']['ids']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_article_list_addon_ids_subtext'],
			'attributes' => array(
				'id'    => 'ids',
				'value' => $context['lp_block']['options']['parameters']['ids'],
				'style' => 'width: 100%'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['direction']['label']['text'] = $txt['lp_article_list_addon_direction'];
		$context['posting_fields']['direction']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'direction'
			),
			'options' => array()
		);

		foreach ($txt['lp_article_list_addon_direction_set'] as $direction => $title) {
			if (RC2_CLEAN) {
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

		$context['posting_fields']['show_images']['label']['text'] = $txt['lp_article_list_addon_show_images'];
		$context['posting_fields']['show_images']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_images',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_images'])
			)
		);
	}

	/**
	 * Get the list of active forum topics
	 *
	 * Получаем список активных тем форума
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getTopics(array $parameters)
	{
		global $smcFunc, $modSettings, $context;

		extract($parameters);

		if (empty($ids))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT m.id_topic, m.id_msg, m.subject, m.body, m.smileys_enabled
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			WHERE t.id_topic IN ({array_int:topics})
				AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}
				AND ml.approved = {int:is_approved}' : '') . '
			ORDER BY t.id_last_msg DESC',
			array(
				'topics'      => explode(',', $ids),
				'is_approved' => 1
			)
		);

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['subject']);
			censorText($row['body']);

			if (!empty($show_images))
				$first_post_image = preg_match('/\[img.*]([^\]\[]+)\[\/img\]/U', $row['body'], $value);

			$image = !empty($first_post_image) ? array_pop($value) : ($modSettings['lp_image_placeholder'] ?? null);

			$topics[$row['id_topic']] = array(
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => Helpers::getTeaser(strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br>' => '&#10;')))),
				'image'       => $image
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $topics;
	}

	/**
	 * Get the list of active pages
	 *
	 * Получаем список активных страниц
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getPages(array $parameters)
	{
		global $smcFunc, $modSettings, $context;

		extract($parameters);

		if (empty($ids))
			return [];

		$titles = Helpers::getFromCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');

		$request = $smcFunc['db_query']('', '
			SELECT page_id, alias, content, description, type
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
				AND page_id IN ({array_int:pages})
			ORDER BY page_id DESC',
			array(
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'pages'        => explode(',', $ids)
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::isFrontpage($row['page_id']))
				continue;

			if (!empty($show_images)) {
				Subs::parseContent($row['content'], $row['type']);
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
			}

			$image = !empty($first_post_image) ? array_pop($value) : null;

			if (empty($image) && !empty($modSettings['lp_image_placeholder']))
				$image = $modSettings['lp_image_placeholder'];

			$pages[$row['page_id']] = array(
				'id'          => $row['page_id'],
				'title'       => $titles[$row['page_id']] ?? [],
				'alias'       => $row['alias'],
				'description' => Helpers::getTeaser($row['description'] ?: strip_tags($row['content'])),
				'image'       => $image
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $pages;
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
		global $user_info, $scripturl, $context, $txt;

		if ($type !== 'article_list')
			return;

		$function     = empty($parameters['article_type']) ? 'getTopics' : 'getPages';
		$article_list = Helpers::getFromCache('article_list_addon_b' . $block_id . '_u' . $user_info['id'], $function, __CLASS__, $cache_time, $parameters);

		ob_start();

		if (!empty($article_list)) {
			loadJavaScriptFile('light_portal/jquery.matchHeight-min.js', array('minimize' => true));
			addInlineJavaScript('
			jQuery(document).ready(function ($) {
				$(".article_list .col-xs > div").matchHeight();
			});', true);

			echo '
		<div class="article_list row', ($parameters['direction'] == 'vertical' ? ' column_direction' : ''), ' between-xs">';

			if (empty($parameters['article_type'])) {
				foreach ($article_list as $topic) {
					echo '
			<div class="col-xs col-sm-6 col-md-4 col-lg-2">';

					$content = '';
					if (!empty($topic['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $topic['image'] . '" alt="">
				</div>';
					}

					$content = '<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['article_body_class'] ?: '_'], $content, null);

					echo '
			</div>';
				}
			} else {
				foreach ($article_list as $page) {
					if (empty($title = Helpers::getPublicTitle($page)))
						continue;

					echo '
			<div class="col-xs col-sm-6 col-md-4 col-lg-2">';

					$content = '';
					if (!empty($page['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $page['image'] . '" alt="">
				</div>';
					}

					$content .= '<a href="' . $scripturl . '?page=' . $page['alias'] . '">' . $title . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['article_body_class'] ?: '_'], $content, null);

					echo '
			</div>';
				}
			}

			echo '
		</div>';
		} else
			echo $txt['lp_article_list_addon_no_items'];

		$content = ob_get_clean();
	}
}

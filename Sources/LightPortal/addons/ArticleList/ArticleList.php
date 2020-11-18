<?php

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Helpers;

/**
 * ArticleList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
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
	 * Display article images (true|false)
	 *
	 * Отображать картинки статей (true|false)
	 *
	 * @var bool
	 */
	private static $seek_images = false;

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
		$options['article_list']['no_content_class'] = static::$no_content_class;

		$options['article_list']['parameters']['article_body_class'] = static::$article_body_class;
		$options['article_list']['parameters']['article_type']       = static::$article_type;
		$options['article_list']['parameters']['ids']                = static::$ids;
		$options['article_list']['parameters']['seek_images']        = static::$seek_images;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public static function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'article_list')
			return;

		$parameters['article_body_class'] = FILTER_SANITIZE_STRING;
		$parameters['article_type']       = FILTER_VALIDATE_INT;
		$parameters['ids']                = FILTER_SANITIZE_STRING;
		$parameters['seek_images']        = FILTER_VALIDATE_BOOLEAN;
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
			'options' => array(),
			'tab' => 'appearance'
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

		$context['posting_fields']['seek_images']['label']['text'] = $txt['lp_article_list_addon_seek_images'];
		$context['posting_fields']['seek_images']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'seek_images',
				'checked' => !empty($context['lp_block']['options']['parameters']['seek_images'])
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
		global $smcFunc, $modSettings;

		if (empty($parameters['ids']))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT m.id_topic, m.id_msg, m.subject, m.body, m.smileys_enabled
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
			WHERE t.id_topic IN ({array_int:topics})
				AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}
				AND ml.approved = {int:is_approved}' : '') . '
			ORDER BY t.id_last_msg DESC',
			array(
				'topics'      => $parameters['ids'],
				'is_approved' => 1
			)
		);

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['subject']);
			censorText($row['body']);

			if (!empty($parameters['seek_images']))
				$first_post_image = preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $row['body'], $value);

			$image = !empty($first_post_image) ? array_pop($value) : ($modSettings['lp_image_placeholder'] ?? null);

			$topics[$row['id_topic']] = array(
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => Helpers::getTeaser(strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br>' => '&#10;')))),
				'image'       => $image
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

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
		global $smcFunc, $modSettings;

		if (empty($parameters['ids']))
			return [];

		$titles = Helpers::getAllTitles();

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
				'pages'        => $parameters['ids']
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::isFrontpage($row['alias']))
				continue;

			if (!empty($parameters['seek_images'])) {
				Helpers::parseContent($row['content'], $row['type']);
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
		$smcFunc['lp_num_queries']++;

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

		$ids = explode(',', $parameters['ids']);
		$parameters['ids'] = array_filter($ids, function($item) {
			return is_numeric($item);
		});

		$function     = empty($parameters['article_type']) ? 'getTopics' : 'getPages';
		$article_list = Helpers::cache('article_list_addon_b' . $block_id . '_u' . $user_info['id'], $function, __CLASS__, $cache_time, $parameters);

		ob_start();

		if (!empty($article_list)) {
			echo '
		<div class="article_list">';

			if (empty($parameters['article_type'])) {
				foreach ($article_list as $topic) {
					$content = '';
					if (!empty($topic['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $topic['image'] . '" loading="lazy" alt="' . $topic['title'] . '">
				</div>';
					}

					$content = '<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['article_body_class'] ?: '_'], $content, null);
				}
			} else {
				foreach ($article_list as $page) {
					if (empty($title = Helpers::getTitle($page)))
						continue;

					$content = '';
					if (!empty($page['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $page['image'] . '" loading="lazy" alt="'. $title . '">
				</div>';
					}

					$content .= '<a href="' . $scripturl . '?page=' . $page['alias'] . '">' . $title . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['article_body_class'] ?: '_'], $content, null);
				}
			}

			echo '
		</div>';
		} else {
			echo '<div class="errorbox">', $txt['lp_article_list_addon_no_items'], '</div>';
		}

		$content = ob_get_clean();
	}
}

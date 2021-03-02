<?php

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Helpers;

/**
 * ArticleList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ArticleList
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-file-alt';

	/**
	 * @var bool
	 */
	private $no_content_class = true;

	/**
	 * @var string
	 */
	private $article_body_class = 'div.descbox';

	/**
	 * @var int
	 */
	private $article_type = 0;

	/**
	 * @var string
	 */
	private $ids = '';

	/**
	 * @var bool
	 */
	private $seek_images = false;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['article_list']['no_content_class'] = $this->no_content_class;

		$options['article_list']['parameters']['article_body_class'] = $this->article_body_class;
		$options['article_list']['parameters']['article_type']       = $this->article_type;
		$options['article_list']['parameters']['ids']                = $this->ids;
		$options['article_list']['parameters']['seek_images']        = $this->seek_images;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'article_list')
			return;

		$parameters['article_body_class'] = FILTER_SANITIZE_STRING;
		$parameters['article_type']       = FILTER_VALIDATE_INT;
		$parameters['ids']                = FILTER_SANITIZE_STRING;
		$parameters['seek_images']        = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
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

			$context['posting_fields']['article_body_class']['input']['options'][$key] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_block']['options']['parameters']['article_body_class']
			);
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
			$context['posting_fields']['article_type']['input']['options'][$title] = array(
				'value'    => $article_type,
				'selected' => $article_type == $context['lp_block']['options']['parameters']['article_type']
			);
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
	public function getTopics(array $parameters)
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
				AND {query_wanna_see_board}
				AND t.approved = {int:is_approved}
				AND ml.approved = {int:is_approved}
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

			$body = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = array(
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => Helpers::getTeaser($body),
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
	public function getPages(array $parameters)
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
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
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

					$content .= '<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

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

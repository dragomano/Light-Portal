<?php

/**
 * ArticleList.php
 *
 * @package ArticleList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class ArticleList extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'far fa-file-alt';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['article_list']['no_content_class'] = true;

		$options['article_list']['parameters'] = [
			'body_class'   => 'descbox',
			'display_type' => 0,
			'ids'          => '',
			'seek_images'  => false
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'article_list')
			return;

		$parameters['body_class']   = FILTER_SANITIZE_STRING;
		$parameters['display_type'] = FILTER_VALIDATE_INT;
		$parameters['ids']          = FILTER_SANITIZE_STRING;
		$parameters['seek_images']  = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'article_list')
			return;

		$data = [];
		foreach ($context['lp_all_content_classes'] as $key => $template) {
			$data[] = "\t\t\t\t" . '{innerHTML: `' . sprintf($template, empty($key) ? $txt['no'] : $key, '') . '`, text: "' . $key . '", selected: ' . ($key == $context['lp_block']['options']['parameters']['body_class'] ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#body_class",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			closeOnSelect: true
		});', true);

		$context['posting_fields']['body_class']['label']['text'] = $txt['lp_article_list']['body_class'];
		$context['posting_fields']['body_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'body_class'
			),
			'options' => array(),
			'tab' => 'appearance'
		);

		$context['posting_fields']['display_type']['label']['text'] = $txt['lp_article_list']['display_type'];
		$context['posting_fields']['display_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'display_type'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_article_list']['display_type_set'] as $article_type => $title) {
			$context['posting_fields']['display_type']['input']['options'][$title] = array(
				'value'    => $article_type,
				'selected' => $article_type == $context['lp_block']['options']['parameters']['display_type']
			);
		}

		$context['posting_fields']['ids']['label']['text'] = $txt['lp_article_list']['ids'];
		$context['posting_fields']['ids']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_article_list']['ids_subtext'],
			'attributes' => array(
				'id'    => 'ids',
				'value' => $context['lp_block']['options']['parameters']['ids'],
				'style' => 'width: 100%'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['seek_images']['label']['text'] = $txt['lp_article_list']['seek_images'];
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
	public function getTopics(array $parameters): array
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
				AND m.approved = {int:is_approved}
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
	public function getPages(array $parameters): array
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
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $scripturl, $context, $txt;

		if ($type !== 'article_list')
			return;

		$ids = explode(',', $parameters['ids']);
		$parameters['ids'] = array_filter($ids, function($item) {
			return is_numeric($item);
		});

		$article_list = Helpers::cache('article_list_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, empty($parameters['display_type']) ? 'getTopics' : 'getPages', $parameters);

		if (!empty($article_list)) {
			echo '
		<div class="article_list">';

			if (empty($parameters['display_type'])) {
				foreach ($article_list as $topic) {
					$content = '';
					if (!empty($topic['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $topic['image'] . '" loading="lazy" alt="' . $topic['title'] . '">
				</div>';
					}

					$content .= '<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['body_class']], $content, null);
				}
			} else {
				foreach ($article_list as $page) {
					if (empty($title = Helpers::getTranslatedTitle($page['title'])))
						continue;

					$content = '';
					if (!empty($page['image'])) {
						$content .= '
				<div class="article_image">
					<img src="' . $page['image'] . '" loading="lazy" alt="'. $title . '">
				</div>';
					}

					$content .= '<a href="' . $scripturl . '?' . LP_PAGE_PARAM . '=' . $page['alias'] . '">' . $title . '</a>';

					echo sprintf($context['lp_all_content_classes'][$parameters['body_class']], $content, null);
				}
			}

			echo '
		</div>';
		} else {
			echo '<div class="errorbox">', $txt['lp_article_list']['no_items'], '</div>';
		}
	}
}

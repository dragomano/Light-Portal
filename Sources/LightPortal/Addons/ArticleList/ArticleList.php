<?php

/**
 * ArticleList.php
 *
 * @package ArticleList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 08.04.23
 */

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\{PageSelect, TopicSelect};

if (! defined('LP_NAME'))
	die('No direct access...');

class ArticleList extends Block
{
	public string $icon = 'far fa-file-alt';

	public function blockOptions(array &$options)
	{
		$options['article_list']['no_content_class'] = true;

		$options['article_list']['parameters'] = [
			'body_class'     => 'descbox',
			'display_type'   => 0,
			'include_topics' => '',
			'include_pages'  => '',
			'seek_images'    => false
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'article_list')
			return;

		$parameters['body_class']     = FILTER_DEFAULT;
		$parameters['display_type']   = FILTER_VALIDATE_INT;
		$parameters['include_topics'] = FILTER_DEFAULT;
		$parameters['include_pages']  = FILTER_DEFAULT;
		$parameters['seek_images']    = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'article_list')
			return;

		$this->context['posting_fields']['body_class']['label']['html'] = '<label for="body_class">' . $this->txt['lp_article_list']['body_class'] . '</label>';
		$this->context['posting_fields']['body_class']['input']['html'] = '<div id="body_class" name="body_class"></div>';
		$this->context['posting_fields']['body_class']['input']['tab']  = 'appearance';

		$this->context['posting_fields']['display_type']['label']['text'] = $this->txt['lp_article_list']['display_type'];
		$this->context['posting_fields']['display_type']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'display_type'
			],
			'options' => [],
			'tab' => 'content'
		];

		foreach ($this->txt['lp_article_list']['display_type_set'] as $article_type => $title) {
			$this->context['posting_fields']['display_type']['input']['options'][$title] = [
				'value'    => $article_type,
				'selected' => $article_type == $this->context['lp_block']['options']['parameters']['display_type']
			];
		}

		$this->context['posting_fields']['include_topics']['label']['html'] = '<label for="include_topics">' . $this->txt['lp_article_list']['include_topics'] . '</label>';
		$this->context['posting_fields']['include_topics']['input']['tab'] = 'content';
		$this->context['posting_fields']['include_topics']['input']['html'] = (new TopicSelect)([
			'id'    => 'include_topics',
			'hint'  => $this->txt['lp_article_list']['include_topics_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_topics'] ?? '',
		]);

		$this->context['posting_fields']['include_pages']['label']['html'] = '<label for="include_pages">' . $this->txt['lp_article_list']['include_pages'] . '</label>';
		$this->context['posting_fields']['include_pages']['input']['tab'] = 'content';
		$this->context['posting_fields']['include_pages']['input']['html'] = (new PageSelect)([
			'id'    => 'include_pages',
			'hint'  => $this->txt['lp_article_list']['include_pages_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_pages'] ?? '',
		]);

		$this->context['posting_fields']['seek_images']['label']['text'] = $this->txt['lp_article_list']['seek_images'];
		$this->context['posting_fields']['seek_images']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'seek_images',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['seek_images']
			]
		];

		$this->setTemplate()->withLayer('article_list');
	}

	public function getTopics(array $parameters): array
	{
		if (empty($parameters['include_topics']))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT m.id_topic, m.id_msg, m.subject, m.body, m.smileys_enabled
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
			WHERE t.id_topic IN ({array_int:topics})
				AND {query_wanna_see_board}
				AND t.approved = {int:is_approved}
				AND m.approved = {int:is_approved}
			ORDER BY t.id_last_msg DESC',
			[
				'topics'      => explode(',', $parameters['include_topics']),
				'is_approved' => 1
			]
		);

		$topics = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['subject']);
			$this->censorText($row['body']);

			$value = null;
			$image = empty($parameters['seek_images']) ? '' : preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $row['body'], $value);
			$image = empty($image) ? ($this->modSettings['lp_image_placeholder'] ?? '') : array_pop($value);

			$body = $this->parseBbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = [
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => $this->getTeaser($body),
				'image'       => $image
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $topics;
	}

	public function getPages(array $parameters): array
	{
		if (empty($parameters['include_pages']))
			return [];

		$titles = $this->getEntityList('title');

		$request = $this->smcFunc['db_query']('', '
			SELECT page_id, alias, content, description, type
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
				AND page_id IN ({array_int:pages})
			ORDER BY page_id DESC',
			[
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'pages'        => explode(',', $parameters['include_pages'])
			]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$row['content'] = parse_content($row['content'], $row['type']);

			$image = empty($parameters['seek_images']) ? '' : $this->getImageFromText($row['content']);

			$pages[$row['page_id']] = [
				'id'          => $row['page_id'],
				'title'       => $titles[$row['page_id']] ?? [],
				'alias'       => $row['alias'],
				'description' => $this->getTeaser($row['description'] ?: strip_tags($row['content'])),
				'image'       => $image ?: ($this->modSettings['lp_image_placeholder'] ?? '')
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'article_list')
			return;

		$article_list = $this->cache('article_list_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(self::class, empty($parameters['display_type']) ? 'getTopics' : 'getPages', $parameters);

		if ($article_list) {
			echo '
		<div class="article_list">';

			if (empty($parameters['display_type'])) {
				foreach ($article_list as $topic) {
					$content = '';
					if ($topic['image']) {
						$content .= '
				<div class="article_image">
					<img src="' . $topic['image'] . '" loading="lazy" alt="' . $topic['title'] . '">
				</div>';
					}

					$content .= '<a href="' . $this->scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

					echo sprintf($this->context['lp_all_content_classes'][$parameters['body_class']], $content, null);
				}
			} else {
				foreach ($article_list as $page) {
					if (empty($title = $this->getTranslatedTitle($page['title'])))
						continue;

					$content = '';
					if ($page['image']) {
						$content .= '
				<div class="article_image">
					<img src="' . $page['image'] . '" loading="lazy" alt="'. $title . '">
				</div>';
					}

					$content .= '<a href="' . LP_PAGE_URL . $page['alias'] . '">' . $title . '</a>';

					echo sprintf($this->context['lp_all_content_classes'][$parameters['body_class']], $content, null);
				}
			}

			echo '
		</div>';
		} else {
			echo '<div class="errorbox">', $this->txt['lp_article_list']['no_items'], '</div>';
		}
	}
}

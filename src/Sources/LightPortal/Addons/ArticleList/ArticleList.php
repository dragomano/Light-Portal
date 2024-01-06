<?php

/**
 * ArticleList.php
 *
 * @package ArticleList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 07.12.23
 */

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, RadioField};
use Bugo\LightPortal\Areas\Partials\{TopicSelect, ContentClassSelect, PageSelect};

if (! defined('LP_NAME'))
	die('No direct access...');

class ArticleList extends Block
{
	public string $icon = 'far fa-file-alt';

	public function blockOptions(array &$options): void
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

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'article_list')
			return;

		$parameters['body_class']     = FILTER_DEFAULT;
		$parameters['display_type']   = FILTER_VALIDATE_INT;
		$parameters['include_topics'] = FILTER_DEFAULT;
		$parameters['include_pages']  = FILTER_DEFAULT;
		$parameters['seek_images']    = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'article_list')
			return;

		CustomField::make('body_class', $this->txt['lp_article_list']['body_class'])
			->setTab('appearance')
			->setValue(fn() => new ContentClassSelect, [
				'id'    => 'body_class',
				'value' => $this->context['lp_block']['options']['parameters']['body_class'],
			]);

		RadioField::make('display_type', $this->txt['lp_article_list']['display_type'])
			->setTab('content')
			->setOptions($this->txt['lp_article_list']['display_type_set'])
			->setValue($this->context['lp_block']['options']['parameters']['display_type']);

		CustomField::make('include_topics', $this->txt['lp_article_list']['include_topics'])
			->setTab('content')
			->setValue(fn() => new TopicSelect, [
				'id'    => 'include_topics',
				'hint'  => $this->txt['lp_article_list']['include_topics_select'],
				'value' => $this->context['lp_block']['options']['parameters']['include_topics'] ?? '',
			]);

		CustomField::make('include_pages', $this->txt['lp_article_list']['include_pages'])
			->setTab('content')
			->setValue(fn() => new PageSelect, [
			'id'    => 'include_pages',
			'hint'  => $this->txt['lp_article_list']['include_pages_select'],
			'value' => $this->context['lp_block']['options']['parameters']['include_pages'] ?? '',
		]);

		CheckboxField::make('seek_images', $this->txt['lp_article_list']['seek_images'])
			->setValue($this->context['lp_block']['options']['parameters']['seek_images']);
	}

	public function getTopics(array $parameters): array
	{
		if (empty($parameters['include_topics']))
			return [];

		$result = $this->smcFunc['db_query']('', '
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
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$this->censorText($row['subject']);
			$this->censorText($row['body']);

			$value = null;
			$image = empty($parameters['seek_images']) ? '' : preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $row['body'], $value);
			$image = $value ? array_pop($value) : ($image ?: $this->modSettings['lp_image_placeholder'] ?? '');

			$body = $this->parseBbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = [
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => $this->getTeaser($body),
				'image'       => $image
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $topics;
	}

	public function getPages(array $parameters): array
	{
		if (empty($parameters['include_pages']))
			return [];

		$titles = $this->getEntityList('title');

		$result = $this->smcFunc['db_query']('', '
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
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
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

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'article_list')
			return;

		$article_list = $this->cache('article_list_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
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

					echo sprintf($this->context['lp_all_content_classes'][$parameters['body_class']], $content);
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

					echo sprintf($this->context['lp_all_content_classes'][$parameters['body_class']], $content);
				}
			}

			echo '
		</div>';
		} else {
			echo '<div class="errorbox">', $this->txt['lp_article_list']['no_items'], '</div>';
		}
	}
}

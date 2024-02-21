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
 * @version 20.02.24
 */

namespace Bugo\LightPortal\Addons\ArticleList;

use Bugo\Compat\{BBCodeParser, Config, Lang, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, RadioField};
use Bugo\LightPortal\Areas\Partials\{ContentClassSelect, PageSelect, TopicSelect};
use Bugo\LightPortal\Utils\Content;

if (! defined('LP_NAME'))
	die('No direct access...');

class ArticleList extends Block
{
	public string $icon = 'far fa-file-alt';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'article_list')
			return;

		$params = [
			'no_content_class' => true,
			'body_class'       => 'descbox',
			'display_type'     => 0,
			'include_topics'   => '',
			'include_pages'    => '',
			'seek_images'      => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'article_list')
			return;

		$params = [
			'body_class'     => FILTER_DEFAULT,
			'display_type'   => FILTER_VALIDATE_INT,
			'include_topics' => FILTER_DEFAULT,
			'include_pages'  => FILTER_DEFAULT,
			'seek_images'    => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'article_list')
			return;

		CustomField::make('body_class', Lang::$txt['lp_article_list']['body_class'])
			->setTab('appearance')
			->setValue(static fn() => new ContentClassSelect(), [
				'id'    => 'body_class',
				'value' => Utils::$context['lp_block']['options']['body_class'],
			]);

		RadioField::make('display_type', Lang::$txt['lp_article_list']['display_type'])
			->setTab('content')
			->setOptions(Lang::$txt['lp_article_list']['display_type_set'])
			->setValue(Utils::$context['lp_block']['options']['display_type']);

		CustomField::make('include_topics', Lang::$txt['lp_article_list']['include_topics'])
			->setTab('content')
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'include_topics',
				'hint'  => Lang::$txt['lp_article_list']['include_topics_select'],
				'value' => Utils::$context['lp_block']['options']['include_topics'] ?? '',
			]);

		CustomField::make('include_pages', Lang::$txt['lp_article_list']['include_pages'])
			->setTab('content')
			->setValue(static fn() => new PageSelect(), [
				'id'    => 'include_pages',
				'hint'  => Lang::$txt['lp_article_list']['include_pages_select'],
				'value' => Utils::$context['lp_block']['options']['include_pages'] ?? '',
			]);

		CheckboxField::make('seek_images', Lang::$txt['lp_article_list']['seek_images'])
			->setValue(Utils::$context['lp_block']['options']['seek_images']);
	}

	public function getTopics(array $parameters): array
	{
		if (empty($parameters['include_topics']))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
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
				'is_approved' => 1,
			]
		);

		$topics = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			Lang::censorText($row['subject']);
			Lang::censorText($row['body']);

			$value = null;
			$image = empty($parameters['seek_images'])
				? ''
				: preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $row['body'], $value);
			$image = $value ? array_pop($value) : ($image ?: Config::$modSettings['lp_image_placeholder'] ?? '');

			$body = BBCodeParser::load()->parse($row['body'], (bool) $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = [
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => $this->getTeaser($body),
				'image'       => $image,
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $topics;
	}

	public function getPages(array $parameters): array
	{
		if (empty($parameters['include_pages']))
			return [];

		$titles = $this->getEntityData('title');

		$result = Utils::$smcFunc['db_query']('', '
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
				'pages'        => explode(',', $parameters['include_pages']),
			]
		);

		$pages = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = empty($parameters['seek_images']) ? '' : $this->getImageFromText($row['content']);

			$pages[$row['page_id']] = [
				'id'          => $row['page_id'],
				'title'       => $titles[$row['page_id']] ?? [],
				'alias'       => $row['alias'],
				'description' => $this->getTeaser($row['description'] ?: strip_tags($row['content'])),
				'image'       => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? ''),
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'article_list')
			return;

		$article_list = $this->cache('article_list_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(
				self::class,
				empty($parameters['display_type']) ? 'getTopics' : 'getPages',
				$parameters
			);

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

					$content .= '<a href="' . Config::$scripturl . '?topic=' . $topic['id'] . '.0">' . $topic['title'] . '</a>';

					echo sprintf(Utils::$context['lp_all_content_classes'][$parameters['body_class']], $content);
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

					echo sprintf(Utils::$context['lp_all_content_classes'][$parameters['body_class']], $content);
				}
			}

			echo '
		</div>';
		} else {
			echo '<div class="errorbox">', Lang::$txt['lp_article_list']['no_items'], '</div>';
		}
	}
}

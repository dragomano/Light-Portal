<?php

/**
 * RecentPosts.php
 *
 * @package RecentPosts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\BlockArea;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, NumberField, RadioField};
use Bugo\LightPortal\Areas\Partials\{TopicSelect, BoardSelect};
use Bugo\LightPortal\Utils\DateTime;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentPosts extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-comment-alt';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_posts')
			return;

		$params = [
			'no_content_class' => true,
			'link_in_title'    => Config::$scripturl . '?action=recent',
			'exclude_boards'   => '',
			'include_boards'   => '',
			'exclude_topics'   => '',
			'include_topics'   => '',
			'use_simple_style' => false,
			'show_avatars'     => false,
			'num_posts'        => 10,
			'link_type'        => 'link',
			'show_body'        => false,
			'limit_body'       => true,
			'update_interval'  => 600,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_posts')
			return;

		$params = [
			'exclude_boards'   => FILTER_DEFAULT,
			'include_boards'   => FILTER_DEFAULT,
			'exclude_topics'   => FILTER_DEFAULT,
			'include_topics'   => FILTER_DEFAULT,
			'use_simple_style' => FILTER_VALIDATE_BOOLEAN,
			'show_avatars'     => FILTER_VALIDATE_BOOLEAN,
			'num_posts'        => FILTER_VALIDATE_INT,
			'link_type'        => FILTER_DEFAULT,
			'show_body'        => FILTER_VALIDATE_BOOLEAN,
			'limit_body'       => FILTER_VALIDATE_BOOLEAN,
			'update_interval'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_posts')
			return;

		CustomField::make('exclude_boards', Lang::$txt['lp_recent_posts']['exclude_boards'])
			->setTab(BlockArea::TAB_CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'exclude_boards',
				'hint'  => Lang::$txt['lp_recent_posts']['exclude_boards_select'],
				'value' => Utils::$context['lp_block']['options']['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', Lang::$txt['lp_recent_posts']['include_boards'])
			->setTab(BlockArea::TAB_CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => Lang::$txt['lp_recent_posts']['include_boards_select'],
				'value' => Utils::$context['lp_block']['options']['include_boards'] ?? '',
			]);

		CustomField::make('exclude_topics', Lang::$txt['lp_recent_posts']['exclude_topics'])
			->setTab(BlockArea::TAB_CONTENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'exclude_topics',
				'hint'  => Lang::$txt['lp_recent_posts']['exclude_topics_select'],
				'value' => Utils::$context['lp_block']['options']['exclude_topics'] ?? '',
			]);

		CustomField::make('include_topics', Lang::$txt['lp_recent_posts']['include_topics'])
			->setTab(BlockArea::TAB_CONTENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'include_topics',
				'hint'  => Lang::$txt['lp_recent_posts']['include_topics_select'],
				'value' => Utils::$context['lp_block']['options']['include_topics'] ?? '',
			]);

		CheckboxField::make('use_simple_style', Lang::$txt['lp_recent_posts']['use_simple_style'])
			->setTab(BlockArea::TAB_APPEARANCE)
			->setAfter(Lang::$txt['lp_recent_posts']['use_simple_style_subtext'])
			->setValue(Utils::$context['lp_block']['options']['use_simple_style']);

		CheckboxField::make('show_avatars', Lang::$txt['lp_recent_posts']['show_avatars'])
			->setTab(BlockArea::TAB_APPEARANCE)
			->setValue(
				Utils::$context['lp_block']['options']['show_avatars']
				&& empty(Utils::$context['lp_block']['options']['use_simple_style'])
			);

		NumberField::make('num_posts', Lang::$txt['lp_recent_posts']['num_posts'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_posts']);

		RadioField::make('link_type', Lang::$txt['lp_recent_posts']['type'])
			->setOptions(array_combine(['link', 'preview'], Lang::$txt['lp_recent_posts']['type_set']))
			->setValue(Utils::$context['lp_block']['options']['link_type']);

		CheckboxField::make('show_body', Lang::$txt['lp_recent_posts']['show_body'])
			->setValue(Utils::$context['lp_block']['options']['show_body']);

		CheckboxField::make('limit_body', Lang::$txt['lp_recent_posts']['limit_body'])
			->setValue(Utils::$context['lp_block']['options']['limit_body']);

		NumberField::make('update_interval', Lang::$txt['lp_recent_posts']['update_interval'])
			->setAttribute('min', 0)
			->setValue(Utils::$context['lp_block']['options']['update_interval']);
	}

	/**
	 * @throws IntlException
	 */
	public function getData(array $parameters): array
	{
		$excludeBoards = empty($parameters['exclude_boards']) ? [] : explode(',', (string) $parameters['exclude_boards']);
		$includeBoards = empty($parameters['include_boards']) ? [] : explode(',', (string) $parameters['include_boards']);
		$excludeTopics = empty($parameters['exclude_topics']) ? [] : explode(',', (string) $parameters['exclude_topics']);
		$includeTopics = empty($parameters['include_topics']) ? [] : explode(',', (string) $parameters['include_topics']);

		$minMessageId = Config::$modSettings['maxMsgID'] - (
			empty(Utils::$context['min_message_posts']) ? 25 : Utils::$context['min_message_posts']
		) * min((int) $parameters['num_posts'], 5);

		$whereQuery = '
			m.id_msg >= {int:min_message_id}' . (empty($excludeBoards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . (empty($includeBoards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . (empty($excludeTopics) ? '' : '
			AND m.id_topic NOT IN ({array_int:exclude_topics})') . (empty($includeTopics) ? '' : '
			AND m.id_topic IN ({array_int:include_topics})') . '
			AND {query_wanna_see_board}' . (Config::$modSettings['postmod_active'] ? '
			AND m.approved = {int:is_approved}' : '');

		$whereQueryParams = [
			'is_approved'    => 1,
			'include_boards' => $includeBoards,
			'exclude_boards' => $excludeBoards,
			'include_topics' => $includeTopics,
			'exclude_topics' => $excludeTopics,
			'min_message_id' => $minMessageId,
		];

		$posts = $this->getFromSsi(
			'queryPosts',
			$whereQuery,
			$whereQueryParams,
			(int) $parameters['num_posts'],
			'm.id_msg DESC',
			'array',
			(bool) $parameters['limit_body']
		);

		if (empty($posts))
			return [];

		array_walk($posts,
			static fn(&$post) => $post['timestamp'] = DateTime::relative((int) $post['timestamp'])
		);

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style']))
			$posts = $this->getItemsWithUserAvatars($posts, 'poster');

		return $posts;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'recent_posts')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_avatars'] ??= false;
		$parameters['limit_body'] ??= false;

		$recentPosts = $this->cache('recent_posts_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recentPosts))
			return;

		$this->setTemplate();

		show_posts($recentPosts, $parameters, $this->isInSidebar($data->id) === false);
	}
}

<?php

/**
 * @package RecentPosts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\RecentPosts;

use Bugo\Compat\{Config, User, Utils};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\UI\Partials\BoardSelect;
use Bugo\LightPortal\UI\Partials\TopicSelect;
use Bugo\LightPortal\Utils\{Avatar, DateTime};

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentPosts extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-comment-alt';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
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

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
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

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('exclude_boards', $this->txt['exclude_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'exclude_boards',
				'hint'  => $this->txt['exclude_boards_select'],
				'value' => $options['exclude_boards'] ?? '',
			]);

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]);

		CustomField::make('exclude_topics', $this->txt['exclude_topics'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'exclude_topics',
				'hint'  => $this->txt['exclude_topics_select'],
				'value' => $options['exclude_topics'] ?? '',
			]);

		CustomField::make('include_topics', $this->txt['include_topics'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'include_topics',
				'hint'  => $this->txt['include_topics_select'],
				'value' => $options['include_topics'] ?? '',
			]);

		CheckboxField::make('use_simple_style', $this->txt['use_simple_style'])
			->setTab(Tab::APPEARANCE)
			->setDescription($this->txt['use_simple_style_subtext'])
			->setValue($options['use_simple_style']);

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setTab(Tab::APPEARANCE)
			->setValue(
				$options['show_avatars']
				&& empty($options['use_simple_style'])
			);

		NumberField::make('num_posts', $this->txt['num_posts'])
			->setAttribute('min', 1)
			->setValue($options['num_posts']);

		RadioField::make('link_type', $this->txt['type'])
			->setOptions(array_combine(['link', 'preview'], $this->txt['type_set']))
			->setValue($options['link_type']);

		CheckboxField::make('show_body', $this->txt['show_body'])
			->setValue($options['show_body']);

		CheckboxField::make('limit_body', $this->txt['limit_body'])
			->setValue($options['limit_body']);

		NumberField::make('update_interval', $this->txt['update_interval'])
			->setAttribute('min', 0)
			->setValue($options['update_interval']);
	}

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

		$posts = $this->getFromSSI(
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
			$posts = Avatar::getWithItems($posts, 'poster');

		return $posts;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		if ($this->request()->has('preview')) {
			$parameters['update_interval'] = 0;
		}

		$parameters['show_avatars'] ??= false;
		$parameters['limit_body'] ??= false;

		$recentPosts = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($recentPosts))
			return;

		$this->setTemplate();

		show_posts($recentPosts, $parameters, $this->isInSidebar($e->args->id) === false);
	}
}

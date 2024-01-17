<?php

/**
 * TagList.php
 *
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\RadioField;
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class TagList extends Block
{
	public string $icon = 'fas fa-tags';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$params['source']  = 'lp_tags';
		$params['sorting'] = 'name';
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$params['source']  = FILTER_DEFAULT;
		$params['sorting'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$sources = array_combine(['lp_tags', 'keywords'], Lang::$txt['lp_tag_list']['source_set']);

		if (! class_exists('\Bugo\Optimus\Keywords'))
			unset($sources['keywords']);

		RadioField::make('source', Lang::$txt['lp_tag_list']['source'])
			->setTab('content')
			->setOptions($sources)
			->setValue(Utils::$context['lp_block']['options']['source']);

		RadioField::make('sorting', Lang::$txt['lp_tag_list']['sorting'])
			->setTab('content')
			->setOptions(array_combine(['name', 'frequency'], Lang::$txt['lp_tag_list']['sorting_set']))
			->setValue(Utils::$context['lp_block']['options']['sorting']);
	}

	public function getAllTopicKeywords(string $sort = 'ok.name'): array
	{
		if (!class_exists('\Bugo\Optimus\Keywords'))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY {raw:sort}',
			[
				'sort' => $sort
			]
		);

		$keywords = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$keywords[] = [
				'value'     => $row['name'],
				'link'      => Config::$scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $keywords;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tag_list = $this->cache('tag_list_addon_b' . $data->block_id . '_u' . User::$info['id'])
				->setLifeTime($data->cache_time)
				->setFallback(Tag::class, 'getAll', 0, 0, $parameters['sorting'] === 'name' ? 'value' : 'num DESC');
		} else {
			$tag_list = $this->cache('tag_list_addon_b' . $data->block_id . '_u' . User::$info['id'])
				->setLifeTime($data->cache_time)
				->setFallback(self::class, 'getAllTopicKeywords', $parameters['sorting'] === 'name' ? 'ok.name' : 'frequency DESC');
		}

		if ($tag_list) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['value'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else {
			echo Lang::$txt['lp_no_tags'];
		}
	}
}

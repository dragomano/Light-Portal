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
 * @version 17.03.24
 */

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\RadioField;

if (! defined('LP_NAME'))
	die('No direct access...');

class TagList extends Block
{
	public string $icon = 'fas fa-tags';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$params = [
			'link_in_title' => Config::$scripturl . '?action=portal;sa=tags',
			'source'        => 'lp_tags',
			'sorting'       => 'name',
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$params = [
			'source'  => FILTER_DEFAULT,
			'sorting' => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'tag_list')
			return;

		$sources = array_combine(['lp_tags', 'keywords'], Lang::$txt['lp_tag_list']['source_set']);

		if (! class_exists('\Bugo\Optimus\Handlers\TagHandler'))
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
		if (! class_exists('\Bugo\Optimus\Handlers\TagHandler'))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY {raw:sort}',
			[
				'sort' => $sort,
			]
		);

		$keywords = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$keywords[] = [
				'title'     => $row['name'],
				'link'      => Config::$scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $keywords;
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tagList = $this->cache('tag_list_addon_b' . $data->id . '_u' . User::$info['id'])
				->setLifeTime($data->cacheTime)
				->setFallback(Tag::class, 'getAll', 0, 0, $parameters['sorting'] === 'name' ? 'title' : 'frequency DESC');
		} else {
			$tagList = $this->cache('tag_list_addon_b' . $data->id . '_u' . User::$info['id'])
				->setLifeTime($data->cacheTime)
				->setFallback(self::class, 'getAllTopicKeywords', $parameters['sorting'] === 'name' ? 'ok.name' : 'frequency DESC');
		}

		if ($tagList) {
			foreach ($tagList as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['icon'] ?? '', $tag['title'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else {
			echo Lang::$txt['lp_no_tags'];
		}
	}
}

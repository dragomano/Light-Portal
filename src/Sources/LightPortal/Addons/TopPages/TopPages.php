<?php

/**
 * TopPages.php
 *
 * @package TopPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 03.06.23
 */

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPages extends Block
{
	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options)
	{
		$options['top_pages']['parameters'] = [
			'popularity_type'   => 'comments',
			'num_pages'         => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_pages')
			return;

		$parameters['popularity_type']   = FILTER_DEFAULT;
		$parameters['num_pages']         = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'top_pages')
			return;

		$this->context['posting_fields']['popularity_type']['label']['text'] = $this->txt['lp_top_pages']['type'];
		$this->context['posting_fields']['popularity_type']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'popularity_type'
			],
			'options' => []
		];

		$types = array_combine(['comments', 'views'], $this->txt['lp_top_pages']['type_set']);

		foreach ($types as $key => $value) {
			$this->context['posting_fields']['popularity_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['popularity_type']
			];
		}

		$this->context['posting_fields']['num_pages']['label']['text'] = $this->txt['lp_top_pages']['num_pages'];
		$this->context['posting_fields']['num_pages']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_pages',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_pages']
			]
		];

		$this->context['posting_fields']['show_numbers_only']['label']['text'] = $this->txt['lp_top_pages']['show_numbers_only'];
		$this->context['posting_fields']['show_numbers_only']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_numbers_only',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_numbers_only']
			]
		];
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityList('title');

		$request = $this->smcFunc['db_query']('', '
			SELECT page_id, alias, type, num_views, num_comments
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			ORDER BY ' . ($parameters['popularity_type'] === 'comments' ? 'num_comments' : 'num_views') . ' DESC
			LIMIT {int:limit}',
			[
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'limit'        => $parameters['num_pages']
			]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = [
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => LP_PAGE_URL . $row['alias']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'top_pages')
			return;

		$parameters['show_numbers_only'] ??= false;

		$top_pages = $this->cache('top_pages_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if ($top_pages) {
			$max = $top_pages[array_key_first($top_pages)]['num_' . $parameters['popularity_type']];

			if (empty($max))
				echo $this->txt['lp_top_pages']['no_items'];
			else {
				echo '
		<dl class="stats">';

				foreach ($top_pages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = $this->getTranslatedTitle($page['title'])))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					echo '
			<dt>
				<a href="', $page['href'], '">', $title, '</a>
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : $this->translate('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $page['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
				}

				echo '
		</dl>';
			}
		} else {
			echo $this->txt['lp_top_pages']['no_items'];
		}
	}
}

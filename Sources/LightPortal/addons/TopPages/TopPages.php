<?php

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\LightPortal\Helpers;

/**
 * TopPages
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

class TopPages
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-balance-scale-left';

	/**
	 * @var string
	 */
	private $type = 'comments';

	/**
	 * @var int
	 */
	private $num_pages = 10;

	/**
	 * @var bool
	 */
	private $show_numbers_only = false;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['top_pages']['parameters']['popularity_type']   = $this->type;
		$options['top_pages']['parameters']['num_pages']         = $this->num_pages;
		$options['top_pages']['parameters']['show_numbers_only'] = $this->show_numbers_only;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'top_pages')
			return;

		$parameters['popularity_type']   = FILTER_SANITIZE_STRING;
		$parameters['num_pages']         = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_pages')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_pages_addon_type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		$types = array_combine(array('coments', 'views'), $txt['lp_top_pages_addon_type_set']);

		foreach ($types as $key => $value) {
			$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
			);
		}

		$context['posting_fields']['num_pages']['label']['text'] = $txt['lp_top_pages_addon_num_pages'];
		$context['posting_fields']['num_pages']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_pages',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_pages']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_pages_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular pages
	 *
	 * Получаем список популярных страниц
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData($parameters)
	{
		global $smcFunc, $scripturl;

		$titles = Helpers::getAllTitles();

		$request = $smcFunc['db_query']('', '
			SELECT page_id, alias, type, num_views, num_comments
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}
				AND created_at <= {int:current_time}
				AND permissions IN ({array_int:permissions})
			ORDER BY ' . ($parameters['popularity_type'] == 'comments' ? 'num_comments' : 'num_views') . ' DESC
			LIMIT {int:limit}',
			array(
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'limit'        => $parameters['num_pages']
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = array(
				'title'        => $titles[$row['page_id']] ?? [],
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'href'         => $scripturl . '?page=' . $row['alias']
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
		global $user_info, $txt;

		if ($type !== 'top_pages')
			return;

		$top_pages = Helpers::cache('top_pages_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $cache_time, $parameters);

		ob_start();

		if (!empty($top_pages)) {
			$max = reset($top_pages)['num_' . $parameters['popularity_type']];

			if (empty($max))
				echo $txt['lp_top_pages_addon_no_items'];
			else {
				echo '
		<dl class="stats">';

				foreach ($top_pages as $page) {
					if ($page['num_' . $parameters['popularity_type']] < 1 || empty($title = Helpers::getTitle($page)))
						continue;

					$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

					echo '
			<dt>
				<a href="', $page['href'], '">', $title, '</a>
			</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : Helpers::getText($page['num_' . $parameters['popularity_type']], $txt['lp_' . $parameters['popularity_type'] . '_set'])), '</span>
			</dd>';
				}

				echo '
		</dl>';
			}
		} else {
			echo $txt['lp_top_pages_addon_no_items'];
		}

		$content = ob_get_clean();
	}
}

<?php declare(strict_types=1);

/**
 * AbstractPageList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPageList
{
	use Helper;

	abstract public function show(Page $page);

	abstract public function getPages(int $start, int $items_per_page, string $sort): array;

	abstract public function getTotalCount(): int;

	abstract public function showAll();

	abstract public function getAll(int $start, int $items_per_page, string $sort): array;

	protected function getPreparedResults(array $rows = []): array
	{
		if (empty($rows))
			return [];

		$items = [];
		foreach ($rows as $row) {
			$row['content'] = parse_content($row['content'], $row['type']);

			$image = null;
			if (! empty($this->modSettings['lp_show_images_in_articles'])) {
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
				$image = $first_post_image ? array_pop($value) : null;
			}

			if (empty($image) && ! empty($this->modSettings['lp_image_placeholder']))
				$image = $this->modSettings['lp_image_placeholder'];

			$items[$row['page_id']] = [
				'id'        => $row['page_id'],
				'alias'     => $row['alias'],
				'author'    => [
					'id'   => $author_id = (int) $row['author_id'],
					'link' => empty($row['author_name']) ? '' : $this->scripturl . '?action=profile;u=' . $author_id,
					'name' => $row['author_name']
				],
				'date'      => $this->getFriendlyTime((int) $row['date']),
				'datetime'  => date('Y-m-d', (int) $row['date']),
				'link'      => LP_PAGE_URL . $row['alias'],
				'views'     => [
					'num'   => $row['num_views'],
					'title' => $this->txt['lp_views']
				],
				'replies'   => [
					'num'   => isset($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'default' ? $row['num_comments'] : 0,
					'title' => $this->txt['lp_comments']
				],
				'title'     => $row['title'],
				'is_new'    => $this->user_info['last_login'] < $row['date'] && $row['author_id'] != $this->user_info['id'],
				'is_front'  => $this->isFrontpage($row['alias']),
				'image'     => $image,
				'can_edit'  => $this->user_info['is_admin'] || ($this->context['allow_light_portal_manage_pages_own'] && $row['author_id'] == $this->user_info['id']),
				'edit_link' => $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id']
			];

			$items[$row['page_id']]['msg_link'] = $items[$row['page_id']]['link'];

			if (! empty($this->modSettings['lp_show_teaser']))
				$items[$row['page_id']]['teaser'] = $this->getTeaser($row['description'] ?: $row['content']);

			if (isset($row['category_id'])) {
				$items[$row['page_id']]['section'] = [
					'name' => $this->getEntityList('category')[$row['category_id']]['name'],
					'link' => LP_BASE_URL . ';sa=categories;id=' . $row['category_id']
				];
			}

			if ($this->context['user']['is_guest']) {
				$items[$row['page_id']]['is_new'] = false;
				$items[$row['page_id']]['views']['num'] = 0;
			}
		}

		return $this->getItemsWithUserAvatars($items);
	}
}

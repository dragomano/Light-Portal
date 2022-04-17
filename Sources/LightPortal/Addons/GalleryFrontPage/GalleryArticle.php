<?php declare(strict_types=1);

/**
 * GalleryArticle.php
 *
 * @package GalleryFrontPage (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.04.22
 */

namespace Bugo\LightPortal\Addons\GalleryFrontPage;

use Bugo\LightPortal\Front\AbstractArticle;

if (! defined('SMF'))
	die('No direct access...');

class GalleryArticle extends AbstractArticle
{
	private array $selected_categories = [];

	public function init()
	{
		isAllowedTo('smfgallery_view');

		$this->selected_categories = smf_json_decode($this->context['lp_gallery_front_page_plugin']['gallery_categories'] ?? '', true);

		$this->params = [
			'approved'            => 1,
			'selected_categories' => $this->selected_categories
		];

		$this->orders = [
			'CASE WHEN (SELECT com.date FROM {db_prefix}gallery_comment AS com WHERE com.id_picture = p.id_picture LIMIT 1) > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.date DESC',
			'p.date',
			'p.date DESC'
		];
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selected_categories))
			return [];

		$this->params += [
			'start' => $start,
			'limit' => $limit
		];

		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.id_picture, p.width, p.height, p.allowcomments, p.id_cat, p.keywords, p.commenttotal AS num_comments, p.filename, p.approved,
				p.views, p.title, p.id_member, m.real_name, p.date, p.description, c.title AS cat_name,
				(SELECT com.date FROM {db_prefix}gallery_comment AS com WHERE com.id_picture = p.id_picture ORDER BY com.date DESC LIMIT 1) AS comment_date' . (empty($this->columns) ? '' : ',
				' . implode(', ', $this->columns)) . '
			FROM {db_prefix}gallery_pic AS p
				LEFT JOIN {db_prefix}gallery_cat AS c ON (c.id_cat = p.id_cat)
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.id_cat IN ({array_int:selected_categories})
				AND p.approved = {int:approved}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty($this->modSettings['lp_frontpage_order_by_replies']) ? '' : 'num_comments DESC, ') . $this->orders[$this->modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$images = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$images[$row['id_picture']] = [
				'id' => $row['id_picture'],
				'section' => [
					'name' => $row['cat_name'],
					'link' => $this->scripturl . '?action=gallery;cat=' . $row['id_cat']
				],
				'author' => [
					'id'   => $row['id_member'],
					'link' => $this->scripturl . '?action=profile;u=' . $row['id_member'],
					'name' => $row['real_name']
				],
				'date'   => $row['date'],
				'title'  => $row['title'],
				'link'   => $this->scripturl . '?action=gallery;sa=view;pic=' . $row['id_picture'],
				'is_new' => false,
				'views' => [
					'num' => $row['views'],
					'title' => $this->txt['lp_views'],
					'after' => ''
				],
				'replies' => [
					'num' => $row['num_comments'],
					'title' => $this->txt['lp_replies'],
					'after' => ''
				],
				'image'     => $this->modSettings['gallery_url'] ?? ($this->boardurl . '/gallery/') . $row['filename'],
				'can_edit'  => $this->user_info['is_admin'] || allowedTo('smfgallery_manage') || (allowedTo('smfgallery_edit') && $row['id_member'] == $this->user_info['id']),
				'edit_link' => $this->scripturl . '?action=gallery;sa=edit;pic=' . $row['id_picture'],
			];

			if (! empty($this->modSettings['lp_show_teaser']))
				$images[$row['id_picture']]['teaser'] = $this->getTeaser($row['description']);

			$images[$row['id_picture']]['msg_link'] = $images[$row['id_picture']]['link'];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $images;
	}

	public function getTotalCount(): int
	{
		if (empty($this->selected_categories))
			return 0;

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(p.id_picture)
			FROM {db_prefix}gallery_pic AS p
				LEFT JOIN {db_prefix}gallery_cat AS c ON (c.id_cat = p.id_cat)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE p.id_cat IN ({array_int:selected_categories})
				AND p.approved = {int:approved}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params
		);

		[$num_images] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_images;
	}
}

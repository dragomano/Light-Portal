<?php declare(strict_types=1);

/**
 * GalleryBlock.php
 *
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 09.04.23
 */

namespace Bugo\LightPortal\Addons\GalleryBlock;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class GalleryBlock extends Block
{
	public string $icon = 'fas fa-image';

	public function blockOptions(array &$options)
	{
		$options['gallery_block']['parameters'] = [
			'categories' => '',
			'num_images' => 10,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'gallery_block')
			return;

		$parameters['categories'] = FILTER_DEFAULT;
		$parameters['num_images'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'gallery_block')
			return;

		$this->context['posting_fields']['categories']['label']['html'] = '<label for="categories">' . $this->txt['lp_gallery_block']['categories'] . '</label>';
		$this->context['posting_fields']['categories']['input']['tab'] = 'content';
		$this->context['posting_fields']['categories']['input']['html'] = (new CategorySelect)();

		$this->context['posting_fields']['num_images']['label']['text'] = $this->txt['lp_gallery_block']['num_images'];
		$this->context['posting_fields']['num_images']['input'] = [
			'type' => 'number',
			'after' => $this->txt['lp_gallery_block']['num_images_subtext'],
			'attributes' => [
				'id'    => 'num_images',
				'min'   => 0,
				'max'   => 999,
				'value' => $this->context['lp_block']['options']['parameters']['num_images']
			]
		];
	}

	public function getData(array $parameters): array
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT
				p.id_picture, p.width, p.height, p.allowcomments, p.id_cat, p.keywords, p.commenttotal AS num_comments, p.filename, p.approved,
				p.views, p.title, p.id_member, m.real_name, p.date, p.description, c.title AS cat_name
			FROM {db_prefix}gallery_pic AS p
				LEFT JOIN {db_prefix}gallery_cat AS c ON (c.id_cat = p.id_cat)
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
			WHERE p.approved = {int:approved}' . (empty($parameters['categories']) ? '' : '
				AND p.id_cat IN ({array_int:categories})') . '
			ORDER BY p.date DESC
			LIMIT {int:limit}',
			[
				'approved'   => 1,
				'categories' => explode(',', $parameters['categories']),
				'limit'      => $parameters['num_images']
			]
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
				'image'     => $this->modSettings['gallery_url'] ?? ($this->boardurl . '/gallery/') . $row['filename'],
				'can_edit'  => $this->user_info['is_admin'] || $this->allowedTo('smfgallery_manage') || ($this->allowedTo('smfgallery_edit') && $row['id_member'] == $this->user_info['id']),
				'edit_link' => $this->scripturl . '?action=gallery;sa=edit;pic=' . $row['id_picture'],
			];

			if (! empty($this->modSettings['lp_show_teaser']))
				$images[$row['id_picture']]['teaser'] = $this->getTeaser($row['description']);
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $images;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'gallery_block')
			return;

		$this->middleware('smfgallery_view');

		$images = $this->cache('gallery_block_addon_u' . $this->context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($images)) {
			echo $this->txt['lp_gallery_block']['no_items'];
			return;
		}

		$is_sidebar = $this->isBlockInPlacements($block_id, ['left', 'right']);

		echo '
		<div class="gallery_block"' . ($is_sidebar ? ' style="grid-auto-flow: row"' : '') . '>';

		foreach ($images as $image) {
			echo '
			<div class="item"><a href="', $image['link'], '"><img src="', $image['image'], '" title="', $image['title'], '" alt="', $image['title'], '"></a></div>';
		}

		echo '
		</div>';
	}
}

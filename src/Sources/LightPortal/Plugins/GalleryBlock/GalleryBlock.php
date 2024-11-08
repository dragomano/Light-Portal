<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\GalleryBlock;

use Bugo\Compat\{Config, Db, Lang, User, Utils};
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class GalleryBlock extends Block
{
	public string $icon = 'fas fa-image';

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'gallery_block')
			return;

		$e->args->params = [
			'link_in_title' => Config::$scripturl . '?action=gallery',
			'categories'    => '',
			'num_images'    => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'gallery_block')
			return;

		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'num_images' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'gallery_block')
			return;

		CustomField::make('categories', Lang::$txt['lp_gallery_block']['categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect());

		NumberField::make('num_images', Lang::$txt['lp_gallery_block']['num_images'])
			->setDescription(Lang::$txt['lp_gallery_block']['num_images_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue(Utils::$context['lp_block']['options']['num_images']);
	}

	public function getData(array $parameters): array
	{
		Db::extend('packages');

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'gallery_pic')))
			return [];

		$categories = empty($parameters['categories']) ? [] : explode(',', (string) $parameters['categories']);

		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				p.id_picture, p.width, p.height, p.allowcomments, p.id_cat, p.keywords, p.commenttotal AS num_comments,
				p.filename, p.approved, p.views, p.title, p.id_member, m.real_name, p.date, p.description, c.title AS cat_name
			FROM {db_prefix}gallery_pic AS p
				LEFT JOIN {db_prefix}gallery_cat AS c ON (c.id_cat = p.id_cat)
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
			WHERE p.approved = {int:approved}' . ($categories ? '
				AND p.id_cat IN ({array_int:categories})' : '') . '
			ORDER BY p.date DESC
			LIMIT {int:limit}',
			[
				'approved'   => 1,
				'categories' => $categories,
				'limit'      => $parameters['num_images'],
			]
		);

		$images = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$images[$row['id_picture']] = [
				'id' => $row['id_picture'],
				'section' => [
					'name' => $row['cat_name'],
					'link' => Config::$scripturl . '?action=gallery;cat=' . $row['id_cat'],
				],
				'author' => [
					'id'   => $row['id_member'],
					'link' => Config::$scripturl . '?action=profile;u=' . $row['id_member'],
					'name' => $row['real_name'],
				],
				'date'   => $row['date'],
				'title'  => $row['title'],
				'link'   => Config::$scripturl . '?action=gallery;sa=view;pic=' . $row['id_picture'],
				'image'     => (Config::$modSettings['gallery_url'] ?? (Config::$boardurl . '/gallery/')) . $row['filename'],
				'can_edit'  => User::hasPermission('smfgallery_manage') || (User::hasPermission('smfgallery_edit') && $row['id_member'] == User::$info['id']),
				'edit_link' => Config::$scripturl . '?action=gallery;sa=edit;pic=' . $row['id_picture'],
			];

			if (! empty(Config::$modSettings['lp_show_teaser'])) {
				$images[$row['id_picture']]['teaser'] = Str::getTeaser($row['description']);
			}
		}

		Utils::$smcFunc['db_free_result']($result);

		return $images;
	}

	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'gallery_block')
			return;

		if (! User::hasPermission('smfgallery_view')) {
			echo Lang::$txt['cannot_smfgallery_view'];
			return;
		}

		$images = $this->cache('gallery_block_addon_b' . $data->id . '_u' . Utils::$context['user']['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($images)) {
			echo Lang::$txt['lp_gallery_block']['no_items'];
			return;
		}

		echo '
		<div class="gallery_block"' . ($this->isInSidebar($data->id) ? ' style="grid-auto-flow: row"' : '') . '>';

		foreach ($images as $image) {
			echo '
			<div class="item">
				<a href="', $image['link'], '">
					<img src="', $image['image'], '" title="', $image['title'], '" alt="', $image['title'], '">
				</a>
			</div>';
		}

		echo '
		</div>';
	}
}

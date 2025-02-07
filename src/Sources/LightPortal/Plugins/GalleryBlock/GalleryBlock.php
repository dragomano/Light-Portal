<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.01.25
 */

namespace Bugo\LightPortal\Plugins\GalleryBlock;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

class GalleryBlock extends Block
{
	public string $icon = 'fas fa-image';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'link_in_title' => Config::$scripturl . '?action=gallery',
			'categories'    => '',
			'num_images'    => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'num_images' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		CustomField::make('categories', $this->txt['categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'categories' => $e->args->options['categories'] ?? '',
			]);

		NumberField::make('num_images', $this->txt['num_images'])
			->setDescription($this->txt['num_images_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue($e->args->options['num_images']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'gallery_pic')))
			return [];

		$categories = empty($parameters['categories']) ? [] : explode(',', (string) $parameters['categories']);

		$result = Db::$db->query('', '
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
				'limit'      => Typed::int($parameters['num_images'], default: 10),
			]
		);

		$images = [];
		while ($row = Db::$db->fetch_assoc($result)) {
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
				'can_edit'  => User::$me->allowedTo('smfgallery_manage') || (User::$me->allowedTo('smfgallery_edit') && $row['id_member'] == User::$info['id']),
				'edit_link' => Config::$scripturl . '?action=gallery;sa=edit;pic=' . $row['id_picture'],
			];

			if (! empty(Config::$modSettings['lp_show_teaser'])) {
				$images[$row['id_picture']]['teaser'] = Str::getTeaser($row['description']);
			}
		}

		Db::$db->free_result($result);

		return $images;
	}

	public function prepareContent(Event $e): void
	{
		if (! User::$me->allowedTo('smfgallery_view')) {
			echo Lang::$txt['cannot_smfgallery_view'];
			return;
		}

		$images = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . Utils::$context['user']['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($e->args->parameters));

		if (empty($images)) {
			echo $this->txt['no_items'];
			return;
		}

		$galleryBlock = Str::html('div', [
			'class' => $this->name,
			'style' => $this->isInSidebar($e->args->id) ? 'grid-auto-flow: row' : null,
		]);

		foreach ($images as $image) {
			$item = Str::html('div', ['class' => 'item']);
			$link = Str::html('a', ['href' => $image['link']]);
			$img = Str::html('img', [
				'src' => $image['image'],
				'title' => $image['title'],
				'alt' => $image['title'],
			]);

			$link->addHtml($img);
			$item->addHtml($link);
			$galleryBlock->addHtml($item);
		}

		echo $galleryBlock;
	}
}

<?php declare(strict_types=1);

/**
 * @package GalleryBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.25
 */

namespace LightPortal\Plugins\GalleryBlock;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\Utils\Str;
use Laminas\Db\Sql\Select;
use Ramsey\Collection\Map\NamedParameterMap;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-image')]
class GalleryBlock extends Block
{
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
			->setValue(static fn() => new CategorySelect([
				'categories' => $e->args->options['categories'] ?? '',
			]));

		NumberField::make('num_images', $this->txt['num_images'])
			->setDescription($this->txt['num_images_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue($e->args->options['num_images']);
	}

	public function getData(NamedParameterMap $parameters): array
	{
		if (! $this->sql->tableExists('gallery_pic')) {
			return [];
		}

		$categories = empty($parameters['categories']) ? [] : explode(',', $parameters['categories']);

		$select = $this->sql->select()
			->from(['p' => 'gallery_pic'])
			->columns([
				'id_picture', 'width', 'height', 'allowcomments', 'id_cat', 'keywords',
				'num_comments' => 'commenttotal', 'filename', 'approved', 'views',
				'title', 'id_member', 'date', 'description',
			])
			->join(['c' => 'gallery_cat'], 'c.id_cat = p.id_cat', ['cat_name' => 'title'], Select::JOIN_LEFT)
			->join(['m' => 'members'], 'm.id_member = p.id_member', ['real_name'], Select::JOIN_LEFT)
			->where(['p.approved' => 1])
			->order('p.date DESC')
			->limit(Str::typed('int', $parameters['num_images'], default: 10));

		if ($categories) {
			$select->where->in('p.id_cat', $categories);
		}

		$result = $this->sql->execute($select);

		$images = [];
		foreach ($result as $row) {
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
				'can_edit'  => $this->isCanEdit($row['id_member']),
				'edit_link' => Config::$scripturl . '?action=gallery;sa=edit;pic=' . $row['id_picture'],
			];

			if (! empty(Config::$modSettings['lp_show_teaser'])) {
				$images[$row['id_picture']]['teaser'] = Str::getTeaser($row['description']);
			}
		}

		return $images;
	}

	public function prepareContent(Event $e): void
	{
		if (! User::$me->allowedTo('smfgallery_view')) {
			echo Lang::$txt['cannot_smfgallery_view'];
			return;
		}

		$images = $this->userCache($this->name . '_addon_b' . $e->args->id)
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
				'src'   => $image['image'],
				'title' => $image['title'],
				'alt'   => $image['title'],
			]);

			$link->addHtml($img);
			$item->addHtml($link);
			$galleryBlock->addHtml($item);
		}

		echo $galleryBlock;
	}

	private function isCanEdit(int $userId): bool
	{
		return User::$me->allowedTo('smfgallery_manage')
			|| (User::$me->allowedTo('smfgallery_edit') && $userId == User::$me->id);
	}
}

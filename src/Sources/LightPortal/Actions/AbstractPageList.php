<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Utils\{Avatar, Content, DateTime};
use Bugo\LightPortal\Utils\{EntityDataTrait, Setting, Str};

use function array_pop;
use function date;
use function preg_match;

use const LP_BASE_URL;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPageList implements PageListInterface
{
	use EntityDataTrait;

	abstract public function show(PageInterface $page);

	abstract public function showAll();

	abstract public function getAll(int $start, int $limit, string $sort): array;

	protected function getPreparedResults(array $rows = []): array
	{
		if ($rows === [])
			return [];

		$items = [];
		foreach ($rows as $row) {
			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[$row['page_id']] = [
				'id'        => (int) $row['page_id'],
				'section'   => $this->getSectionData($row),
				'slug'      => $row['slug'],
				'author'    => $this->getAuthorData($row),
				'date'      => DateTime::relative((int) $row['date']),
				'datetime'  => date('Y-m-d', (int) $row['date']),
				'link'      => $this->getLink($row),
				'msg_link'  => $this->getLink($row),
				'views'     => $this->getViewsData($row),
				'replies'   => $this->getRepliesData($row),
				'title'     => $row['title'],
				'is_new'    => $this->isNew($row),
				'is_front'  => Setting::isFrontpage($row['slug']),
				'image'     => $this->getImage($row),
				'can_edit'  => $this->canEdit($row),
				'edit_link' => $this->getEditLink($row),
			];

			$this->prepareTeaser($items, $row);

			if (Utils::$context['user']['is_guest']) {
				$items[$row['page_id']]['is_new'] = false;
				$items[$row['page_id']]['views']['num'] = 0;
			}
		}

		return Avatar::getWithItems($items);
	}

	private function getSectionData(array $row): array
	{
		if (empty($categories = $this->getEntityData('category')))
			return [];

		if (isset($row['category_id'])) {
			return [
				'name' => $categories[$row['category_id']]['title'],
				'link' => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
			];
		}

		return [];
	}

	private function getAuthorData(array $row): array
	{
		return [
			'id'   => $authorId = (int) $row['author_id'],
			'link' => empty($row['author_name']) ? '' : Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $row['author_name'],
		];
	}

	private function getLink(array $row): string
	{
		return LP_PAGE_URL . $row['slug'];
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => (int) $row['num_views'],
			'title' => Lang::$txt['lp_views'],
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => Setting::getCommentBlock() === 'default' ? (int) $row['num_comments'] : 0,
			'title' => Lang::$txt['lp_comments'],
		];
	}

	private function isNew(array $row): bool
	{
		return User::$info['last_login'] < $row['date'] && (int) $row['author_id'] !== User::$info['id'];
	}

	private function getImage(array $row): string
	{
		$image = '';

		if (! empty(Config::$modSettings['lp_show_images_in_articles'])) {
			$firstPostImage = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', (string) $row['content'], $value);
			$image = $firstPostImage ? array_pop($value) : null;
		}

		if (empty($image) && ! empty(Config::$modSettings['lp_image_placeholder'])) {
			$image = Config::$modSettings['lp_image_placeholder'];
		}

		return $image;
	}

	private function canEdit(array $row): bool
	{
		if (User::$info['is_admin'])
			return true;

		return Utils::$context['allow_light_portal_manage_pages_own'] && (int) $row['author_id'] === User::$info['id'];
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $row['page_id'];
	}

	private function prepareTeaser(array &$items, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$items[$row['page_id']]['teaser'] = Str::getTeaser($row['description'] ?: $row['content']);
	}
}

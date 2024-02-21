<?php declare(strict_types=1);

/**
 * CategoryConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Repositories\CategoryRepository;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryConfig extends AbstractConfig
{
	private CategoryRepository $repository;

	public function __construct()
	{
		$this->repository = new CategoryRepository();
	}

	public function show(): void
	{
		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['sub_template'] = 'lp_category_settings';

		Utils::$context['page_title'] = Lang::$txt['lp_categories'];

		Utils::$context['lp_categories'] = $this->getEntityData('category');

		unset(Utils::$context['lp_categories'][0]);

		if ($this->request()->has('actions')) {
			$data = $this->request()->json();

			if (isset($data['new_name']))
				$this->add($data['new_name'], $data['new_desc'] ?? '');

			if (isset($data['update_priority']))
				$this->repository->updatePriority($data['update_priority']);

			if (isset($data['name']))
				$this->repository->updateName((int) $data['item'], $data['name']);

			if (isset($data['desc']))
				$this->repository->updateDescription((int) $data['item'], $data['desc']);

			if (isset($data['del_item']))
				$this->repository->remove([(int) $data['del_item']]);

			exit;
		}
	}

	private function add(string $name, string $desc = ''): void
	{
		if (empty($name))
			return;

		Theme::loadTemplate('LightPortal/ManageSettings');

		$result = [
			'error' => true
		];

		$item = $this->repository->add($name, $desc);

		if ($item) {
			ob_start();

			show_single_category($item, ['name' => $name, 'desc' => $desc]);

			$newCategory = ob_get_clean();

			$result = [
				'success' => true,
				'section' => $newCategory,
				'item'    => $item,
			];
		}

		$this->cache()->forget('all_categories');

		exit(json_encode($result));
	}
}

<?php declare(strict_types=1);

/**
 * SiteArticle.php
 *
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license Individual (for sponsors)
 *
 * @category addon
 * @version 07.12.23
 */

namespace Bugo\LightPortal\Addons\SiteList;

use Bugo\LightPortal\Front\AbstractArticle;

if (! defined('SMF'))
	die('No direct access...');

class SiteArticle extends AbstractArticle
{
	private array $sites = [];

	public function init(): void
	{
		$this->sites = $this->jsonDecode($this->context['lp_site_list_plugin']['urls'] ?? '');
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->sites))
			return [];

		$items = [];
		foreach ($this->sites as $url => $data) {
			$items[] = [
				'title'     => $data[1] ?: $url,
				'is_new'    => false,
				'edit_link' => $this->scripturl . '?action=admin;area=lp_plugins',
				'can_edit'  => $this->user_info['is_admin'],
				'link'      => $url,
				'msg_link'  => $url,
				'teaser'    => $data[2] ?? '',
				'image'     => $data[0] ?: ('https://mini.s-shot.ru/?' . urlencode($url))
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty($this->sites))
			return 0;

		return count($this->sites);
	}
}

<?php declare(strict_types=1);

/**
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license Individual (for sponsors)
 *
 * @category addon
 * @version 10.02.24
 */

namespace Bugo\LightPortal\Plugins\SiteList;

use Bugo\Compat\{Config, User, Utils};
use Bugo\LightPortal\Articles\AbstractArticle;

if (! defined('SMF'))
	die('No direct access...');

class SiteArticle extends AbstractArticle
{
	private array $sites = [];

	public function init(): void
	{
		$this->sites = Utils::jsonDecode(Utils::$context['lp_site_list_plugin']['urls'] ?? '', true);
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
				'edit_link' => Config::$scripturl . '?action=admin;area=lp_plugins',
				'can_edit'  => User::$info['is_admin'],
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

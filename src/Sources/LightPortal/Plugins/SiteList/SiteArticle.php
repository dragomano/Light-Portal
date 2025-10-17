<?php declare(strict_types=1);

/**
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license Individual (for sponsors)
 *
 * @category plugin
 * @version 13.10.25
 */

namespace Bugo\LightPortal\Plugins\SiteList;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Articles\AbstractArticle;
use Laminas\Db\Sql\Select;

if (! defined('LP_NAME'))
	die('No direct access...');

class SiteArticle extends AbstractArticle
{
	private array $sites = [];

	public function init(): void
	{
		$this->sites = Utils::jsonDecode(Utils::$context['lp_site_list_plugin']['urls'] ?? '', true);
	}

	public function getSortingOptions(): array
	{
		return [];
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		foreach ($this->sites as $url => $data) {
			$item = [
				'title'     => $data[1] ?: $url,
				'is_new'    => false,
				'edit_link' => Config::$scripturl . '?action=admin;area=lp_plugins',
				'can_edit'  => User::$me->is_admin,
				'link'      => $url,
				'teaser'    => $data[2] ?? '',
				'image'     => $data[0] ?: ('https://mini.s-shot.ru/?' . urlencode($url))
			];

			yield $url => $item;
		}
	}

	public function getTotalCount(): int
	{
		if (empty($this->sites))
			return 0;

		return count($this->sites);
	}

	protected function applyBaseConditions(Select $select): void {}
}

<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils\Traits;

use Bugo\Compat\Utils;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Utils\Setting;

trait HasSorting
{
	use HasSession;

	public function prepareSortingOptions(ArticleInterface $article): void
	{
		Utils::$context['lp_sorting_options'] = $article->getSortingOptions();
	}

	public function prepareSorting(string $sessionKey): void
	{
		$sort = $this->request()->get('sort');

		if ($sort !== null) {
			Utils::$context['lp_current_sorting'] = $sort;
		} elseif (! $this->session('lp')->isEmpty($sessionKey)) {
			Utils::$context['lp_current_sorting'] = $this->session('lp')->get($sessionKey);
		} else {
			$sort = Setting::get('lp_frontpage_article_sorting', 'string', 'created;desc');
			Utils::$context['lp_current_sorting'] = $sort;
		}

		$this->session('lp')->put($sessionKey, Utils::$context['lp_current_sorting']);
	}
}

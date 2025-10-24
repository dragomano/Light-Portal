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

namespace LightPortal\Articles;

use LightPortal\Articles\Services\PageArticleService;

if (! defined('SMF'))
	die('No direct access...');

class PageArticle extends AbstractArticle implements PageArticleInterface
{
	public function __construct(PageArticleService $service)
	{
		parent::__construct($service);
	}

	public function prepareTags(array &$pages): void
	{
		($this->service instanceof PageArticleService) && $this->service->prepareTags($pages);
	}
}

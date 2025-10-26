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

namespace LightPortal\Articles\Services;

use LightPortal\Articles\Queries\TagPageArticleQuery;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;

if (! defined('SMF'))
	die('No direct access...');

class TagPageArticleService extends PageArticleService
{
	public function __construct(
		TagPageArticleQuery $query,
		EventDispatcherInterface $dispatcher,
		PageRepositoryInterface $repository
	)
	{
		parent::__construct($query, $dispatcher, $repository);
	}
}

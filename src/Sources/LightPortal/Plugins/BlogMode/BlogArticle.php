<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.10.25
 */

namespace LightPortal\Plugins\BlogMode;

use LightPortal\Articles\PageArticle;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlogArticle extends PageArticle
{
	public const TYPE = 'blog';
}

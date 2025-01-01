<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\BlogMode;

use Bugo\LightPortal\Articles\PageArticle;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlogArticle extends PageArticle
{
	public const TYPE = 'blog';

	public function init(): void
	{
		parent::init();

		$this->selectedCategories = [];

		$this->params['entry_type'] = self::TYPE;
	}
}

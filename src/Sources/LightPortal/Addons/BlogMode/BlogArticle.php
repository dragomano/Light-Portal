<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.06.24
 */

namespace Bugo\LightPortal\Addons\BlogMode;

use Bugo\LightPortal\Articles\PageArticle;

if (! defined('SMF'))
	die('No direct access...');

class BlogArticle extends PageArticle
{
	public const STATUS = 4;

	public function init(): void
	{
		parent::init();

		$this->selectedCategories = [];

		$this->params['status'] = self::STATUS;
	}
}

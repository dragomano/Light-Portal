<?php declare(strict_types=1);

/**
 * AbstractArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractArticle implements ArticleInterface
{
	use Helper;

	protected array $columns = [];
	protected array $tables  = [];
	protected array $wheres  = [];
	protected array $params  = [];
	protected array $orders  = [];

	abstract public function init(): void;

	abstract public function getData(int $start, int $limit): array;

	abstract public function getTotalCount(): int;
}

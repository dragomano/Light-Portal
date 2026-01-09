<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Actions;

use LightPortal\Repositories\RepositoryInterface;
use LightPortal\Utils\Traits\HasBreadcrumbs;
use LightPortal\Utils\Traits\HasTablePresenter;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractIndex implements IndexInterface
{
	use HasBreadcrumbs;
	use HasTablePresenter;

	public function __construct(protected RepositoryInterface $repository) {}
}

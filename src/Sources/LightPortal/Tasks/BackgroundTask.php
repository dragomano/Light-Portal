<?php declare(strict_types=1);

/**
 * BackgroundTask.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Tasks;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\Utils;
use SMF_BackgroundTask;

abstract class BackgroundTask extends SMF_BackgroundTask
{
	use Helper;

	public function __construct(array $details)
	{
		new Utils();

		parent::__construct($details);
	}
}

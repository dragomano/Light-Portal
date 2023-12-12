<?php declare(strict_types=1);

/**
 * TextField.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Fields;

if (! defined('SMF'))
	die('No direct access...');

class TextField extends InputField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		$this->setType('text');
	}
}

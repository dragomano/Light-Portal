<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Fields;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

class VirtualSelectField extends SelectField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		Theme::addInlineJavaScript('
		VirtualSelect.init({
			ele: "#' . $name . '",
			hideClearButton: true,' . (Utils::$context['right_to_left'] ? '
			textDirection: "rtl",' : '') . '
			dropboxWrapper: "body"
		});', true);
	}
}

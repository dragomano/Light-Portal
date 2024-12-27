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

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\RequestTrait;

if (! defined('SMF'))
	die('No direct access...');

final class FeedbackConfig extends AbstractConfig
{
	use RequestTrait;

	public function show(): void
	{
		Theme::loadTemplate('LightPortal/ManageFeedback');

		Utils::$context['page_title'] = Lang::$txt['lp_feedback'];

		Utils::$context['success_url'] = Config::$scripturl . '?action=admin;area=lp_settings;sa=feedback;success';

		Utils::$context['feedback_sent'] = $this->request()->has('success');

		Utils::$context['sub_template'] = 'feedback';
	}
}

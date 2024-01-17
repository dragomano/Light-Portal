<?php declare(strict_types=1);

/**
 * FeedbackConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class FeedbackConfig
{
	use Helper;

	public function show(): void
	{
		$this->loadTemplate('LightPortal/ManageFeedback');

		Utils::$context['page_title'] = Lang::$txt['lp_feedback'];

		Utils::$context['success_url'] = Config::$scripturl . '?action=admin;area=lp_settings;sa=feedback;success';

		Utils::$context['feedback_sent'] = $this->request()->has('success');

		Utils::$context['sub_template'] = 'feedback';
	}
}

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

if (! defined('SMF'))
	die('No direct access...');

final class FeedbackConfig
{
	use Helper;

	public function show(): void
	{
		$this->loadTemplate('LightPortal/ManageFeedback');

		$this->context['page_title'] = $this->txt['lp_feedback'];

		$this->context['success_url'] = $this->scripturl . '?action=admin;area=lp_settings;sa=feedback;success';

		$this->context['feedback_sent'] = $this->request()->has('success');

		$this->context['sub_template'] = 'feedback';
	}
}

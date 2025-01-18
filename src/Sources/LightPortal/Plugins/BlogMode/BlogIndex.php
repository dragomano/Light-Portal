<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 21.12.24
 */

namespace Bugo\LightPortal\Plugins\BlogMode;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\FrontPage;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlogIndex
{
	private readonly FrontPage $front;

	private readonly string $action;

	public function __construct()
	{
		$this->front = app(FrontPage::class);

		$this->action = Utils::$context['lp_blog_mode_plugin']['blog_action'] ?? '';
	}

	public function show(): void
	{
		$this->front->show();

		Utils::$context['canonical_url'] = Config::$scripturl . '?action=' . $this->action;

		Utils::$context['page_title'] = Utils::$context['forum_name'] . ' - ' . Lang::$txt['lp_blog_mode']['menu_item_title'];

		unset(Utils::$context['linktree'][1]);

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_blog_mode']['menu_item_title'],
		];
	}
}

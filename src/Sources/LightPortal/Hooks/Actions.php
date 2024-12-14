<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\RequestTrait;

use function array_search;
use function implode;

use const LP_ACTION;

if (! defined('SMF'))
	die('No direct access...');

class Actions
{
	use CommonChecks;
	use RequestTrait;

	public function __invoke(array &$actions): void
	{
		if (Setting::get('lp_frontpage_mode', 'bool', false)) {
			$actions[LP_ACTION] = [false, [new FrontPage(), 'show']];
		}

		$actions['forum'] = [false, [new BoardIndex(), 'show']];

		Theme::load();

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'categories') {
			(new Category())->show(new Page());
		}

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'tags') {
			(new Tag())->show(new Page());
		}

		if ($this->request()->is(LP_ACTION) && Utils::$context['current_subaction'] === 'promote') {
			$this->promoteTopic();
		}

		if (empty(Config::$modSettings['lp_standalone_mode']))
			return;

		$this->unsetDisabledActions($actions);
		$this->redirectFromDisabledActions();
	}

	protected function promoteTopic(): void
	{
		if (empty(User::$info['is_admin']) || $this->request()->hasNot('t'))
			return;

		$topic = $this->request('t');

		$frontpageTopics = Setting::getFrontpageTopics();

		if (($key = array_search($topic, $frontpageTopics)) !== false) {
			unset($frontpageTopics[$key]);
		} else {
			$frontpageTopics[] = $topic;
		}

		Config::updateModSettings(
			['lp_frontpage_topics' => implode(',', $frontpageTopics)]
		);

		Utils::redirectexit('topic=' . $topic);
	}
}

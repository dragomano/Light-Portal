<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Actions\BoardIndex;
use LightPortal\Actions\Category;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\Tag;
use LightPortal\Enums\Action;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Traits\HasCache;

use function LightPortal\app;

use const LP_ACTION;

if (! defined('SMF'))
	die('No direct access...');

class Actions
{
	use HasCache;
	use HasCommonChecks;

	public function __invoke(array &$actions): void
	{
		if (Setting::get('lp_frontpage_mode', 'string', '')) {
			$actions[LP_ACTION] = [false, [app(FrontPage::class), 'show']];
		}

		$this->handleSubActions();

		$actions[Action::FORUM->value] = [false, [app(BoardIndex::class), 'show']];

		if (empty(Config::$modSettings['lp_standalone_mode']))
			return;

		$this->unsetDisabledActions($actions);
		$this->redirectFromDisabledActions();
	}

	private function handleSubActions(): void
	{
		$sa = $this->request()->get('sa') ?? '';

		if ($this->request()->is(LP_ACTION)) {
			match ($sa) {
				PortalSubAction::CATEGORIES->name() => app(Category::class)->show(),
				PortalSubAction::TAGS->name()       => app(Tag::class)->show(),
				PortalSubAction::PROMOTE->name()    => $this->promoteTopic(),
				default                             => null,
			};
		}
	}

	private function promoteTopic(): void
	{
		if (empty(User::$me->is_admin) || $this->request()->hasNot('t'))
			return;

		$topic = $this->request()->get('t');

		$homeTopics = Setting::getFrontpageTopics();

		if (($key = array_search($topic, $homeTopics)) !== false) {
			unset($homeTopics[$key]);
		} else {
			$homeTopics[] = $topic;
		}

		Config::updateModSettings(
			['lp_frontpage_topics' => implode(',', $homeTopics)]
		);

		$this->cache()->flush();

		$this->response()->redirect('topic=' . $topic);
	}
}

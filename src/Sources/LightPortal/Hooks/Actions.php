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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Traits\HasCache;

use function array_search;
use function implode;

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

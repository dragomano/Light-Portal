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

namespace LightPortal\Renderers;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use eftec\bladeone\BladeOne;
use Exception;
use LightPortal\Enums\Tab;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class Blade extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.blade.php';

	public const DEFAULT_EXTENSION = '.blade.php';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout)) {
			return '';
		}

		ob_start();

		try {
			$blade = $this->createBladeInstance();

			if (str_ends_with($layout, self::DEFAULT_EXTENSION)) {
				$layout = substr($layout, 0, -strlen(self::DEFAULT_EXTENSION));
			}

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}

	public function renderString(string $string, array $params = []): string
	{
		if (empty($string)) {
			return '';
		}

		ob_start();

		try {
			$blade = $this->createBladeInstance();

			echo $blade->runString($string, $params);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}

	private function createBladeInstance(): BladeOne
	{
		$blade = new BladeOne([$this->templateDir, $this->customDir], Sapi::getTempDir());
		$blade->setBaseUrl(Theme::$current->settings['default_theme_url'] . '/scripts/light_portal');

		$this->setupDirectives($blade);

		return $blade;
	}

	private function setupDirectives(BladeOne $blade): void
	{
		$blade->directiveRT('icon', static function (mixed $name, string $title = '') {
			if (is_array($name)) {
				[$n, $t] = count($name) > 1 ? $name : [$name[0], ''];
				$name = $n;
				$title = $t;
			}

			$icon = Icon::get($name);

			if (empty($title)) {
				echo $icon;
				return;
			}

			echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
		});

		$blade->directiveRT('postErrors', static function () {
			if (empty(Utils::$context['post_errors'])) {
				return;
			}

			$div = Str::html('div')->class('errorbox');
			$ul = Str::html('ul');

			foreach (Utils::$context['post_errors'] as $error) {
				$ul->addHtml(Str::html('li')->addHtml($error));
			}

			echo $div->addHtml($ul);
		});

		$blade->directiveRT('portalTab', static function (array $fields, Tab|string $tab = 'content') {
			$fields['subject'] = ['no'];

			$tabName = is_string($tab) ? $tab : $tab->name();

			foreach ($fields as $pfid => $pf) {
				if (empty($pf['input']['tab'])) {
					$pf['input']['tab'] = Tab::TUNING->name();
				}

				if ($pf['input']['tab'] != $tabName) {
					$fields[$pfid] = ['no'];
				}
			}

			Utils::$context['posting_fields'] = $fields;

			Theme::loadTemplate('Post');

			ob_start();
			template_post_header();
			echo ob_get_clean();
		});
	}
}

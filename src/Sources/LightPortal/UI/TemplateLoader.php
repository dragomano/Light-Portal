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

namespace LightPortal\UI;

use Bugo\Compat\{ErrorHandler, Lang, Theme, Utils};

if (! defined('SMF'))
	die('No direct access...');

class TemplateLoader
{
	private static ?View $view = null;

	private static string $content = '';

	public static function fromFile(
		string $template = '',
		array $params = [],
		bool $useSubTemplate = true
	): string|bool
	{
		if (empty($template)) {
			return false;
		}

		if (self::templateExists($template)) {
			return self::renderTemplate($template, $params, $useSubTemplate);
		}

		ErrorHandler::fatal('[LP] ' . sprintf(Lang::$txt['theme_template_error'], $template), false);

		return false;
	}

	public static function getLastContent(): string
	{
		return self::$content ?? '';
	}

	private static function renderTemplate(string $template, array $params, bool $useSubTemplate = true): string
	{
		self::initView();

		$bladeContent = self::$view->render($template, $params);

		if ($useSubTemplate) {
			/* @uses template_lp_blade_wrapper */
			Utils::$context['sub_template'] = 'lp_blade_wrapper';

			self::$content = $bladeContent;
		}

		return $bladeContent;
	}

	private static function templateExists(string $template): bool
	{
		return file_exists(self::getTemplatePath($template));
	}

	private static function getTemplatePath(string $template): string
	{
		return self::getTemplateBasePath() . "/$template.blade.php";
	}

	private static function initView(): void
	{
		if (self::$view === null) {
			self::$view = new View(self::getTemplateBasePath(), '');
		}
	}

	private static function getTemplateBasePath(): string
	{
		return Theme::$current->settings['default_theme_dir'] . '/LightPortal';
	}
}

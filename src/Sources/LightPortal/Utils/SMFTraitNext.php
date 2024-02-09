<?php declare(strict_types=1);

/**
 * SMFTraitNext.php (special for SMF 3.0)
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use SMF\Actions\Admin\ACP;
use SMF\Actions\Admin\Permissions;
use SMF\Actions\MessageIndex;
use SMF\Config;
use SMF\Editor;
use SMF\IntegrationHook;
use SMF\ItemList;
use SMF\Lang;
use SMF\Logging;
use SMF\Msg;
use SMF\PageIndex;
use SMF\Security;
use SMF\Utils;
use SMF\WebFetch\WebFetchApi;

if (! defined('SMF'))
	die('No direct access...');

/**
 * @method getCamelName(string $name)
 */
trait SMFTraitNext
{
	public function permissionsList(array &$permissions): void
	{
		Lang::$txt['permissiongroup_light_portal'] = LP_NAME;

		Permissions::$permission_groups['global'][] = 'light_portal';
		Permissions::$left_permission_groups[] = 'light_portal';

		$permissions['light_portal_view'] = [
			'view_group' => 'light_portal',
			'scope' => 'global',
		];

		$permissions['light_portal_manage_pages_own'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any' => 'own',
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_manage_pages_any'] = [
			'generic_name' => 'light_portal_manage_pages',
			'own_any' => 'any',
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];

		$permissions['light_portal_approve_pages'] = [
			'view_group' => 'light_portal',
			'scope' => 'global',
			'never_guests' => true,
		];
	}

	protected function applyHook(string $name, string $method = ''): void
	{
		$name = str_replace('integrate_', '', $name);

		if ($name === 'load_illegal_guest_permissions')
			return;

		if ($name === 'load_permissions') {
			$name = 'permissions_list';
		}

		if (func_num_args() === 1) {
			$method = lcfirst($this->getCamelName($name));
		}

		$method = static::class . '::' . str_replace('#', '', $method);

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

		if ($name === 'init') {
			$name = 'pre_load';
		}

		IntegrationHook::add('integrate_' . $name, $method . '#', false, $file);
	}

	protected function createControlRichedit(array $editorOptions): void
	{
		Editor::load($editorOptions);

		Utils::$context['post_box_name'] = $editorOptions['id'];

		Theme::addJavaScriptVar('oEditorID', Utils::$context['post_box_name'], true);
		Theme::addJavaScriptVar('oEditorObject', 'oEditorHandle_' . Utils::$context['post_box_name'], true);

		ob_start();

		template_control_richedit(Utils::$context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		Utils::$context['posting_fields']['content']['label']['html'] = '<label>' . Lang::$txt['lp_content'] . '</label>';
		Utils::$context['posting_fields']['content']['input']['html'] = ob_get_clean();
		Utils::$context['posting_fields']['content']['input']['tab'] = 'content';
	}

	protected function fetchWebData(string $url): bool|string
	{
		return WebFetchApi::fetch($url);
	}

	protected function createList(array $listOptions): void
	{
		new ItemList($listOptions);

		Utils::$context['sub_template'] = 'show_list';
		Utils::$context['default_list'] = $listOptions['id'];
	}

	protected function checkSubmitOnce(string $action): void
	{
		Security::checkSubmitOnce($action);
	}

	protected function preparseCode(string &$message): void
	{
		Msg::preparsecode($message);
	}

	protected function unPreparseCode(string $message): array|string|null
	{
		return Msg::un_preparsecode($message);
	}

	protected function saveDBSettings(array $save_vars): void
	{
		ACP::saveDBSettings($save_vars);
	}

	protected function prepareDBSettingContext(array $config_vars): void
	{
		ACP::prepareDBSettingContext($config_vars);
	}

	protected function dbExtend(string $type = 'extra'): void
	{
		// It's not necessary in SMF 3.0
	}

	protected function getBoardList(array $options = []): array
	{
		$defaultOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => empty(Config::$modSettings['recycle_board']) ? null : [(int) Config::$modSettings['recycle_board']],
		];

		if (isset($options['included_boards']))
			unset($defaultOptions['excluded_boards']);

		return MessageIndex::getBoardList(array_merge($defaultOptions, $options));
	}

	protected function constructPageIndex(string $base_url, &$start, int $max_value, int $num_per_page): object
	{
		$start = (int) $start;

		return new PageIndex($base_url, $start, $max_value, $num_per_page);
	}

	protected function logAction(string $action, array $extra = []): int
	{
		return Logging::logAction($action, $extra);
	}
}

<?php

/**
 * CrowdinContext.php
 *
 * @package CrowdinContext (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.12.23
 */

namespace Bugo\LightPortal\Addons\CrowdinContext;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class CrowdinContext extends Plugin
{
	public string $type = 'other';

	public function addSettings(array &$config_vars): void
	{
		$config_vars['crowdin_context'][] = ['multiselect', 'admins', $this->getAdminList()];
	}

	public function init(): void
	{
		if ($this->isCanUse() === false)
			return;

		$this->applyHook('actions');

		$this->loadLanguage('LightPortal/LightPortal', 'crowdin');

		$addons = $this->getEntityList('plugin');
		array_walk($addons, function ($addon) {
			if (is_file($file = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR . 'crowdin.php')) {
				$this->txt['lp_' . $this->getSnakeName($addon)] = require $file;
			}
		});

		$this->addInlineJavaScript('
		var _jipt = [];
		_jipt.push([\'project\', \'light-portal\']);
		_jipt.push([\'preload_texts\', true]);
		_jipt.push([\'escape\', function() {
			window.location.href = smf_scripturl + "?action=' . LP_ACTION . ';disable_crowdin";
		}]);');

		$this->loadExtJS('//cdn.crowdin.com/jipt/jipt.js', ['defer' => true]);
	}

	public function actions(): void
	{
		if ($this->request()->is(LP_ACTION) && $this->request()->has('disable_crowdin')) {
			if ($key = array_search('CrowdinContext', $this->context['lp_enabled_plugins'])) {
				unset($this->context['lp_enabled_plugins'][$key]);

				$this->updateSettings(['lp_enabled_plugins' => implode(',', $this->context['lp_enabled_plugins'])]);

				$this->redirect('action=admin;area=lp_plugins');
			}
		}
	}

	private function isCanUse(): bool
	{
		return ! empty($this->user_info['is_admin']) && isset($this->context['lp_crowdin_context_plugin']['admins']) && in_array($this->user_info['id'], explode(',', $this->context['lp_crowdin_context_plugin']['admins']));
	}

	private function getAdminList(): array
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_group = {int:id_group}
				OR FIND_IN_SET({int:id_group}, additional_groups) != 0',
			[
				'id_group' => 1,
			]
		);

		$users = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result))
			$users[$row['id_member']] = $row['real_name'];

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $users;
	}
}

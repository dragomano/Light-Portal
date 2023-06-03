<?php

/**
 * CrowdinContext.php
 *
 * @package CrowdinContext (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.04.23
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
		$this->addDefaultValues([
			'admin_id' => $this->user_info['id'],
		]);

		$config_vars['crowdin_context'][] = ['select', 'admin_id', $this->getAdminList()];
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
			window.location.href = smf_scripturl + "?action=portal;disable_crowdin";
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
		return ! empty($this->user_info['is_admin']) && isset($this->context['lp_crowdin_context_plugin']['admin_id']) && (int) $this->context['lp_crowdin_context_plugin']['admin_id'] === $this->user_info['id'];
	}

	private function getAdminList(): array
	{
		$ids = $this->membersAllowedTo('admin_forum');

		$users = $this->loadUserInfo($ids);

		return array_column($users, 'name', 'id');
	}
}
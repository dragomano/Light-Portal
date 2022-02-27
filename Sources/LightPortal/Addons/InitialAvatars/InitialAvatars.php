<?php

/**
 * InitialAvatars.php
 *
 * @package InitialAvatars (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 27.02.22
 */

namespace Bugo\LightPortal\Addons\InitialAvatars;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class InitialAvatars extends Plugin
{
	public string $type = 'other';

	public function init()
	{
		add_integration_function('integrate_member_context', __CLASS__ . '::memberContext#', false, __FILE__);
	}

	public function addSettings(array &$config_vars)
	{
		$addSettings = [];
		if (! isset($this->modSettings['lp_initial_avatars_addon_size']))
			$addSettings['lp_initial_avatars_addon_size'] = 48;
		if (! isset($this->modSettings['lp_initial_avatars_addon_back_color']))
			$addSettings['lp_initial_avatars_addon_back_color'] = '#f0e9e9';
		if (! isset($this->modSettings['lp_initial_avatars_addon_font_color']))
			$addSettings['lp_initial_avatars_addon_font_color'] = '#8b5d5d';
		if (! isset($this->modSettings['lp_initial_avatars_addon_font_size']))
			$addSettings['lp_initial_avatars_addon_font_size'] = .5;
		if (! isset($this->modSettings['lp_initial_avatars_addon_length']))
			$addSettings['lp_initial_avatars_addon_length'] = 2;
		if ($addSettings)
			updateSettings($addSettings);

		$config_vars['initial_avatars'][] = ['check', 'replace_only_missing'];
		$config_vars['initial_avatars'][] = ['check', 'auto_font'];
		$config_vars['initial_avatars'][] = ['int', 'size', 'postfix' => $this->txt['quote_expand_pixels_units']];
		$config_vars['initial_avatars'][] = ['color', 'back_color'];
		$config_vars['initial_avatars'][] = ['color', 'font_color'];
		$config_vars['initial_avatars'][] = ['float', 'font_size', 'max' => 1];
		$config_vars['initial_avatars'][] = ['int', 'length'];
		$config_vars['initial_avatars'][] = ['select', 'driver', ['GD', 'Imagick']];
		$config_vars['initial_avatars'][] = ['check', 'rounded'];
	}

	public function memberContext(array &$userData, int $user)
	{
		if (! empty($this->modSettings['lp_initial_avatars_addon_replace_only_missing']) && $userData['avatar']['url'] !== $this->modSettings['avatar_url'] . '/default.png')
			return;

		$data = $this->cache('initial_avatars_addon_u' . $user)->setFallback(__CLASS__, 'getAvatar', $userData['name']);

		$userData['avatar']['image'] = $data[$userData['name']];
	}

	public function getAvatar(string $name): array
	{
		require_once __DIR__ . '/vendor/autoload.php';

		$avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();

		$avatar = $avatar->name($name);

		if (! empty($this->modSettings['lp_initial_avatars_addon_auto_font']))
			$avatar = $avatar->autoFont();

		if (! empty($this->modSettings['lp_initial_avatars_addon_size']))
			$avatar = $avatar->size($this->modSettings['lp_initial_avatars_addon_size']);

		if (! empty($this->modSettings['lp_initial_avatars_addon_back_color']))
			$avatar = $avatar->background($this->modSettings['lp_initial_avatars_addon_back_color']);

		if (! empty($this->modSettings['lp_initial_avatars_addon_font_color']))
			$avatar = $avatar->color($this->modSettings['lp_initial_avatars_addon_font_color']);

		if (! empty($this->modSettings['lp_initial_avatars_addon_font_size']))
			$avatar = $avatar->fontSize($this->modSettings['lp_initial_avatars_addon_font_size']);

		if (! empty($this->modSettings['lp_initial_avatars_addon_length']))
			$avatar = $avatar->length($this->modSettings['lp_initial_avatars_addon_length']);

		if (! empty($this->modSettings['lp_initial_avatars_addon_driver']))
			$avatar = $avatar->imagick();

		if (! empty($this->modSettings['lp_initial_avatars_addon_rounded']))
			$avatar = $avatar->rounded();

		return [$name => $avatar->generateSvg()->toXMLString()];
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Initial Avatar Generator',
			'link' => 'https://github.com/LasseRafn/php-initial-avatar-generator',
			'author' => 'Lasse Rafn',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/LasseRafn/php-initial-avatar-generator/blob/master/LICENSE.md'
			]
		];
	}
}

<?php

/**
 * Likely.php
 *
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.01.24
 */

namespace Bugo\LightPortal\Addons\Likely;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Fields\RadioField;

if (! defined('LP_NAME'))
	die('No direct access...');

class Likely extends Block
{
	public string $icon = 'far fa-share-square';

	private array $buttons = ['facebook', 'linkedin', 'odnoklassniki', 'pinterest', 'reddit', 'telegram', 'twitter', 'viber', 'vkontakte', 'whatsapp'];

	public function prepareBlockParams(array &$params): void
	{
		if ($this->context['current_block']['type'] !== 'likely')
			return;

		$params = [
			'size'      => 'small',
			'dark_mode' => false,
			'buttons'   => $this->buttons,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if ($this->context['current_block']['type'] !== 'likely')
			return;

		$params = [
			'size'      => FILTER_DEFAULT,
			'dark_mode' => FILTER_VALIDATE_BOOLEAN,
			'buttons'   => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['current_block']['type'] !== 'likely')
			return;

		CustomField::make('buttons', $this->txt['lp_likely']['buttons'])
			->setTab('content')
			->setValue(fn() => new ButtonSelect, [
				'data'  => $this->buttons,
				'value' => is_array($this->context['lp_block']['options']['buttons'])
							? $this->context['lp_block']['options']['buttons']
							: explode(',', $this->context['lp_block']['options']['buttons'])
			]);

		RadioField::make('size', $this->txt['lp_likely']['size'])
			->setOptions($this->txt['lp_likely']['size_set'])
			->setValue($this->context['lp_block']['options']['size']);

		CheckboxField::make('dark_mode', $this->txt['lp_likely']['dark_mode'])
			->setValue($this->context['lp_block']['options']['dark_mode']);
	}

	public function prepareAssets(array &$assets): void
	{
		$assets['css']['likely'][] = 'https://cdn.jsdelivr.net/npm/ilyabirman-likely@3/release/likely.min.css';
		$assets['scripts']['likely'][] = 'https://cdn.jsdelivr.net/npm/ilyabirman-likely@3/release/likely.min.js';
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'likely' || empty($parameters['buttons']))
			return;

		$this->loadCSSFile('light_portal/likely/likely.min.css');
		$this->loadJSFile('light_portal/likely/likely.min.js', ['minimize' => true]);

		echo /** @lang text */ '
			<div class="centertext likely_links">
				<div class="likely likely-', $parameters['size'], (empty($parameters['dark_mode']) ? '' : ' likely-dark-theme'), '">';

		$buttons = is_array($parameters['buttons']) ? $parameters['buttons'] : explode(',', $parameters['buttons']);

		foreach ($buttons as $service) {
			if (empty($this->txt['lp_likely']['buttons_set'][$service]))
				continue;

			echo '
					<div class="', $service, '" tabindex="0" role="link" aria-label="', $this->txt['lp_likely']['buttons_set'][$service], '"', (! empty($this->modSettings['optimus_tw_cards']) && $service === 'twitter' ? ' data-via="' . $this->modSettings['optimus_tw_cards'] . '"' : ''), (! empty($this->settings['og_image']) && $service === 'pinterest' ? ' data-media="' . $this->settings['og_image'] . '"' : ''), (! empty($this->settings['og_image']) && $service === 'odnoklassniki' ? ' data-imageurl="' . $this->settings['og_image'] . '"' : ''), '>', $this->txt['lp_likely']['buttons_set'][$service], '</div>';
		}

		echo /** @lang text */ '
				</div>
			</div>';
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Likely',
			'link' => 'https://github.com/NikolayRys/Likely',
			'author' => 'Nikolay Rys, Ilya Birman, Evgeny Steblinsky, Artem Sapegin',
			'license' => [
				'name' => 'the ISC License',
				'link' => 'https://github.com/NikolayRys/Likely/blob/master/license.txt'
			]
		];
	}
}

<?php

/**
 * Likely.php
 *
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 4.03.23
 */

namespace Bugo\LightPortal\Addons\Likely;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Likely extends Plugin
{
	public string $icon = 'far fa-share-square';

	private array $buttons = ['facebook', 'linkedin', 'odnoklassniki', 'pinterest', 'reddit', 'telegram', 'twitter', 'viber', 'vkontakte', 'whatsapp'];

	public function blockOptions(array &$options)
	{
		$options['likely']['parameters']['size']    = 'small';
		$options['likely']['parameters']['skin']    = 'normal';
		$options['likely']['parameters']['buttons'] = $this->buttons;
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'likely')
			return;

		$parameters['size']    = FILTER_DEFAULT;
		$parameters['skin']    = FILTER_DEFAULT;
		$parameters['buttons'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'likely')
			return;

		$this->context['posting_fields']['size']['label']['text'] = $this->txt['lp_likely']['size'];
		$this->context['posting_fields']['size']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'size'
			],
			'options' => []
		];

		foreach ($this->txt['lp_likely']['size_set'] as $value => $title) {
			$this->context['posting_fields']['size']['input']['options'][$title] = [
				'value'    => $value,
				'selected' => $value == $this->context['lp_block']['options']['parameters']['size']
			];
		}

		$this->context['posting_fields']['skin']['label']['text'] = $this->txt['lp_likely']['skin'];
		$this->context['posting_fields']['skin']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'skin'
			],
			'options' => []
		];

		foreach ($this->txt['lp_likely']['skin_set'] as $value => $title) {
			$this->context['posting_fields']['skin']['input']['options'][$title] = [
				'value'    => $value,
				'selected' => $value == $this->context['lp_block']['options']['parameters']['skin']
			];
		}

		if (! is_array($this->context['lp_block']['options']['parameters']['buttons'])) {
			$this->context['lp_block']['options']['parameters']['buttons'] = explode(',', $this->context['lp_block']['options']['parameters']['buttons']);
		}

		$this->context['posting_fields']['buttons']['label']['html'] = '<label for="buttons">' . $this->txt['lp_likely']['buttons'] . '</label>';
		$this->context['posting_fields']['buttons']['input']['html'] = '<div id="buttons" name="buttons"></div>';
		$this->context['posting_fields']['buttons']['input']['tab']  = 'content';

		$this->context['likely_buttons'] = $this->buttons;

		$this->setTemplate()->withLayer('likely');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'likely')
			return;

		if (empty($parameters['buttons']))
			return;

		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.css');
		$this->loadExtJS('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.js');

		echo '
			<div class="centertext likely_links">
				<div class="likely likely-', $parameters['size'], ($parameters['skin'] == 'light' ? ' likely-light' : ''), '">';

		$buttons = is_array($parameters['buttons']) ? $parameters['buttons'] : explode(',', $parameters['buttons']);

		foreach ($buttons as $service) {
			if (! empty($this->txt['lp_likely']['buttons_set'][$service])) {
				echo '
					<div class="', $service, '" tabindex="0" role="link" aria-label="', $this->txt['lp_likely']['buttons_set'][$service], '"', (! empty($this->modSettings['optimus_tw_cards']) && $service === 'twitter' ? ' data-via="' . $this->modSettings['optimus_tw_cards'] . '"' : ''), (! empty($this->settings['og_image']) && $service === 'pinterest' ? ' data-media="' . $this->settings['og_image'] . '"' : ''), '>', $this->txt['lp_likely']['buttons_set'][$service], '</div>';
			}
		}

		echo '
				</div>
			</div>';
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Likely',
			'link' => 'https://github.com/NikolayRys/Likely',
			'author' => 'Artem Sapegin, Evgeny Steblinsky, Ilya Birman',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/NikolayRys/Likely/blob/master/license.txt'
			]
		];
	}
}

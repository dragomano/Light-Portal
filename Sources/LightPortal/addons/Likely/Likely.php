<?php

/**
 * Likely
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\Likely;

use Bugo\LightPortal\Addons\Plugin;

class Likely extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'far fa-share-square';

	/**
	 * @var array
	 */
	private $buttons = ['facebook', 'linkedin', 'odnoklassniki', 'pinterest', 'reddit', 'telegram', 'twitter', 'viber', 'vkontakte', 'whatsapp'];

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['likely']['parameters']['size']    = 'small';
		$options['likely']['parameters']['skin']    = 'normal';
		$options['likely']['parameters']['buttons'] = $this->buttons;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'likely')
			return;

		$parameters['size']    = FILTER_SANITIZE_STRING;
		$parameters['skin']    = FILTER_SANITIZE_STRING;
		$parameters['buttons'] = array(
			'name'   => 'buttons',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'likely')
			return;

		$context['posting_fields']['size']['label']['text'] = $txt['lp_likely']['size'];
		$context['posting_fields']['size']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'size'
			),
			'options' => array()
		);

		foreach ($txt['lp_likely']['size_set'] as $value => $title) {
			$context['posting_fields']['size']['input']['options'][$title] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_block']['options']['parameters']['size']
			);
		}

		$context['posting_fields']['skin']['label']['text'] = $txt['lp_likely']['skin'];
		$context['posting_fields']['skin']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'skin'
			),
			'options' => array()
		);

		foreach ($txt['lp_likely']['skin_set'] as $value => $title) {
			$context['posting_fields']['skin']['input']['options'][$title] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_block']['options']['parameters']['skin']
			);
		}

		if (!is_array($context['lp_block']['options']['parameters']['buttons'])) {
			$context['lp_block']['options']['parameters']['buttons'] = explode(',', $context['lp_block']['options']['parameters']['buttons']);
		}

		$data = [];
		foreach ($this->buttons as $button) {
			$data[] = "\t\t\t\t" . '{text: "' . $button . '", selected: ' . (in_array($button, $context['lp_block']['options']['parameters']['buttons']) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#buttons",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $txt['lp_likely']['select_buttons'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$context['posting_fields']['buttons']['label']['text'] = $txt['lp_likely']['buttons'];
		$context['posting_fields']['buttons']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'buttons',
				'name'     => 'buttons[]',
				'multiple' => true,
				'style'    => 'height: auto'
			),
			'options' => array(),
			'tab' => 'content'
		);
	}

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $txt, $modSettings, $settings;

		if ($type !== 'likely')
			return;

		if (empty($parameters['buttons']))
			return;

		loadCSSFile('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.css', array('external' => true));
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.js', array('external' => true));

		echo '
			<div class="centertext likely_links">
				<div class="likely likely-', $parameters['size'], ($parameters['skin'] == 'light' ? ' likely-light' : ''), '">';

		$buttons = is_array($parameters['buttons']) ? $parameters['buttons'] : explode(',', $parameters['buttons']);

		foreach ($buttons as $service) {
			if (!empty($txt['lp_likely']['buttons_set'][$service])) {
				echo '
					<div class="', $service, '" tabindex="0" role="link" aria-label="', $txt['lp_likely']['buttons_set'][$service], '"', (!empty($modSettings['optimus_tw_cards']) && $service == 'twitter' ? ' data-via="' . $modSettings['optimus_tw_cards'] . '"' : ''), (!empty($settings['og_image']) && $service == 'pinterest' ? ' data-media="' . $settings['og_image'] . '"' : ''), '>', $txt['lp_likely']['buttons_set'][$service], '</div>';
			}
		}

		echo '
				</div>
			</div>';
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(array &$links)
	{
		$links[] = array(
			'title' => 'Likely',
			'link' => 'https://github.com/NikolayRys/Likely',
			'author' => 'Artem Sapegin, Evgeny Steblinsky, Ilya Birman',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/NikolayRys/Likely/blob/master/license.txt'
			)
		);
	}
}

<?php

namespace Bugo\LightPortal\Addons\Likely;

/**
 * Likely
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Likely
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-share-square';

	/**
	 * @var string
	 */
	private $size = 'small';

	/**
	 * @var string
	 */
	private $skin = 'normal';

	/**
	 * @var string
	 */
	private $buttons = 'facebook,twitter,vkontakte,pinterest,odnoklassniki,telegram,linkedin,whatsapp';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['likely']['parameters']['size']    = $this->size;
		$options['likely']['parameters']['skin']    = $this->skin;
		$options['likely']['parameters']['buttons'] = $this->buttons;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'likely')
			return;

		$parameters['size']    = FILTER_SANITIZE_STRING;
		$parameters['skin']    = FILTER_SANITIZE_STRING;
		$parameters['buttons'] = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'likely')
			return;

		$context['posting_fields']['size']['label']['text'] = $txt['lp_likely_addon_size'];
		$context['posting_fields']['size']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'size'
			)
		);

		$context['posting_fields']['size']['input']['options'] = array(
			'small' => array(
				'value'    => 'small',
				'selected' => 'small' == $context['lp_block']['options']['parameters']['size']
			),
			'big' => array(
				'value'    => 'big',
				'selected' => 'big' == $context['lp_block']['options']['parameters']['size']
			)
		);

		$context['posting_fields']['skin']['label']['text'] = $txt['lp_likely_addon_skin'];
		$context['posting_fields']['skin']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'skin'
			)
		);

		$context['posting_fields']['skin']['input']['options'] = array(
			'normal' => array(
				'value'    => 'normal',
				'selected' => 'normal' == $context['lp_block']['options']['parameters']['skin']
			),
			'light' => array(
				'value'    => 'light',
				'selected' => 'light' == $context['lp_block']['options']['parameters']['skin']
			)
		);

		$context['posting_fields']['buttons']['label']['text'] = $txt['lp_likely_addon_buttons'];
		$context['posting_fields']['buttons']['input'] = array(
			'type' => 'textarea',
			'after' => sprintf($txt['lp_likely_addon_buttons_subtext'], $this->buttons),
			'attributes' => array(
				'id'        => 'buttons',
				'maxlength' => 255,
				'value'     => $context['lp_block']['options']['parameters']['buttons']
			),
			'tab' => 'content'
		);
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $txt, $modSettings, $settings;

		if ($type !== 'likely')
			return;

		if (!empty($parameters['buttons'])) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/ilyabirman-likely@2/release/likely.min.js', array('external' => true));

			ob_start();

			echo '
			<div class="centertext likely_links">
				<div class="likely likely-', $parameters['size'], ($parameters['skin'] == 'dark' ? ' likely-light' : ''), '">';

			$buttons = explode(',', $parameters['buttons']);

			foreach ($buttons as $service) {
				if (!empty($txt['lp_likely_addon_buttons_set'][$service])) {
					echo '
					<div class="', $service, '" tabindex="0" role="link" aria-label="', $txt['lp_likely_addon_buttons_set'][$service], '"', (!empty($modSettings['optimus_tw_cards']) && $service == 'twitter' ? ' data-via="' . $modSettings['optimus_tw_cards'] . '"' : ''), (!empty($settings['og_image']) && $service == 'pinterest' ? ' data-media="' . $settings['og_image'] . '"' : ''), '>', $txt['lp_likely_addon_buttons_set'][$service], '</div>';
				}
			}

			echo '
				</div>
			</div>';

			$content = ob_get_clean();
		}
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
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

<?php

namespace Bugo\LightPortal\Addons\Likely;

/**
 * Likely
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Likely
{
	/**
	 * Размер кнопок (small|big)
	 *
	 * @var string
	 */
	private static $size = 'small';

	/**
	 * Скин кнопок (normal|light)
	 *
	 * @var string
	 */
	private static $skin = 'normal';

	/**
	 * Список отображаемых кнопок
	 *
	 * @var string
	 */
	private static $buttons = 'facebook,twitter,vkontakte,pinterest,odnoklassniki,telegram,linkedin,whatsapp';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['likely'] = array(
			'parameters' => array(
				'skin'    => static::$skin,
				'size'    => static::$size,
				'buttons' => static::$buttons
			)
		);
	}

	/**
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'likely')
			return;

		$args['parameters'] = array(
			'size'    => FILTER_SANITIZE_STRING,
			'skin'    => FILTER_SANITIZE_STRING,
			'buttons' => FILTER_SANITIZE_STRING
		);
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
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

		if (!defined('JQUERY_VERSION')) {
			$context['posting_fields']['size']['input']['options'] = array(
				'small' => array(
					'attributes' => array(
						'value'    => 'small',
						'selected' => 'small' == $context['lp_block']['options']['parameters']['size']
					)
				),
				'big' => array(
					'attributes' => array(
						'value'    => 'big',
						'selected' => 'big' == $context['lp_block']['options']['parameters']['size']
					)
				)
			);
		} else {
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
		}

		$context['posting_fields']['skin']['label']['text'] = $txt['lp_likely_addon_skin'];
		$context['posting_fields']['skin']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'skin'
			)
		);

		if (!defined('JQUERY_VERSION')) {
			$context['posting_fields']['skin']['input']['options'] = array(
				'normal' => array(
					'attributes' => array(
						'value'    => 'normal',
						'selected' => 'normal' == $context['lp_block']['options']['parameters']['skin']
					)
				),
				'light' => array(
					'attributes' => array(
						'value'    => 'light',
						'selected' => 'light' == $context['lp_block']['options']['parameters']['skin']
					)
				)
			);
		} else {
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
		}

		$context['posting_fields']['buttons']['label']['text'] = $txt['lp_likely_addon_buttons'];
		$context['posting_fields']['buttons']['input'] = array(
			'type' => 'textarea',
			'after' => sprintf($txt['lp_likely_addon_buttons_subtext'], static::$buttons),
			'attributes' => array(
				'id' => 'buttons',
				'maxlength' => 255,
				'value' => $context['lp_block']['options']['parameters']['buttons']
			)
		);
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt, $modSettings, $settings;

		if ($type !== 'likely')
			return;

		if (!empty($parameters['buttons'])) {
			loadCSSFile('https://unpkg.com/ilyabirman-likely@2/release/likely.css', array('external' => true));
			loadJavaScriptFile('https://unpkg.com/ilyabirman-likely@2/release/likely.js', array('external' => true));

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
	 * Добавляем копирайты плагина
	 *
	 * @param array $links
	 * @return void
	 */
	public static function credits(&$links)
	{
		$links[] = array(
			'title' => 'Likely',
			'link' => 'https://github.com/NikolayRys/Likely',
			'author' => '2013 Artem Sapegin, 2015 Evgeny Steblinsky, 2015 Ilya Birman',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/NikolayRys/Likely/blob/master/license.txt'
			)
		);
	}
}

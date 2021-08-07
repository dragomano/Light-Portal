<?php

namespace Bugo\LightPortal;

/**
 * Manage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

trait Manage
{
	/**
	 * @see https://github.com/brianvoe/slim-select
	 *
	 * @return void
	 */
	public function improveSelectFields()
	{
		loadCSSFile('https://cdn.jsdelivr.net/npm/slim-select@1/dist/slimselect.min.css', array('external' => true));
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/slim-select@1/dist/slimselect.min.js', array('external' => true));

		addInlineCss('
		.ss-content.ss-open {
			position: initial;
		}
		.ss-disabled {
			color: inherit !important;
		}');
	}

	/**
	 * Prepare field array with entity options
	 *
	 * Формируем массив полей с настройками сущности
	 *
	 * @return void
	 */
	public function preparePostFields()
	{
		global $context;

		foreach ($context['posting_fields'] as $item => $data) {
			if ($item !== 'icon' && !empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="descbox alternative2 smalltext">' . $data['input']['after'] . '</div>';

			if (isset($data['input']['type']) && $data['input']['type'] == 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $data['input']['attributes']['id'] . '"></label>' . ($context['posting_fields'][$item]['input']['after'] ?? '');
				$context['posting_fields'][$item] = $data;
			}

			if (empty($data['input']['tab']))
				$context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		loadTemplate('LightPortal/ManageSettings');
	}
}

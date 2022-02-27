<?php

/**
 * Pug.php
 *
 * @package Pug (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 27.02.22
 */

namespace Bugo\LightPortal\Addons\Pug;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Pug extends Plugin
{
	public string $icon = 'fas fa-bone';
	public string $type = 'parser';

	public function init()
	{
		$this->context['lp_content_types']['pug'] = 'Pug';
	}

	public function parseContent(string &$content, string $type)
	{
		if ($type === 'pug')
			$content = $this->getParsedContent($content);
	}

	private function getParsedContent(string $text): string
	{
		require_once __DIR__ . '/vendor/autoload.php';

		$pug = new \Pug([
			'expressionLanguage' => 'js',
			'strict' => true,
		]);

		try {
			return $pug->render(un_htmlspecialchars($text));
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Pug PHP',
			'link' => 'https://github.com/pug-php/pug',
			'author' => 'Kyle and contributors',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/pug-php/pug/blob/master/LICENSE'
			]
		];
	}
}

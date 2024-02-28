<?php declare(strict_types=1);

/**
 * BlockAppearance.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

trait BlockAppearance
{
	/**
	 * Get a list of all used classes for blocks with a header
	 *
	 * Получаем список всех используемых классов для блоков с заголовком
	 */
	private function getTitleClasses(): array
	{
		return [
			'cat_bar'              => '<div class="cat_bar"><h3 class="catbg">%1$s</h3></div>',
			'title_bar'            => '<div class="title_bar"><h3 class="titlebg">%1$s</h3></div>',
			'sub_bar'              => '<div class="sub_bar"><h3 class="subbg">%1$s</h3></div>',
			'noticebox'            => '<div class="noticebox"><h3>%1$s</h3></div>',
			'infobox'              => '<div class="infobox"><h3>%1$s</h3></div>',
			'descbox'              => '<div class="descbox"><h3>%1$s</h3></div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper"><h3>%1$s</h3></div>',
			'progress_bar'         => '<div class="progress_bar"><h3>%1$s</h3></div>',
			'popup_content'        => '<div class="popup_content"><h3>%1$s</h3></div>',
			''                     => '<div>%1$s</div>',
		];
	}

	/**
	 * Get a list of all used classes for blocks with content
	 *
	 * Получаем список всех используемых классов для блоков с контентом
	 */
	private function getContentClasses(): array
	{
		return [
			'roundframe'           => '<div class="roundframe noup">%1$s</div>',
			'roundframe2'          => '<div class="roundframe">%1$s</div>',
			'windowbg'             => '<div class="windowbg noup">%1$s</div>',
			'windowbg2'            => '<div class="windowbg">%1$s</div>',
			'information'          => '<div class="information">%1$s</div>',
			'errorbox'             => '<div class="errorbox">%1$s</div>',
			'noticebox'            => '<div class="noticebox">%1$s</div>',
			'infobox'              => '<div class="infobox">%1$s</div>',
			'descbox'              => '<div class="descbox">%1$s</div>',
			'bbc_code'             => '<div class="bbc_code">%1$s</div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper">%1$s</div>',
			''                     => '<div>%1$s</div>',
		];
	}
}

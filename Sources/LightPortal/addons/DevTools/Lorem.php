<?php

namespace Bugo\LightPortal\Addons\DevTools;

/**
 * DevTools
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

 /**
  * https://stackoverflow.com/a/39986034/14091866
  */
abstract class Lorem
{
	/**
	 * @var array
	 */
	private static $lorem = ['lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'praesent', 'interdum', 'dictum', 'mi', 'non', 'egestas', 'nulla', 'in', 'lacus', 'sed', 'sapien', 'placerat', 'malesuada', 'at', 'erat', 'etiam', 'id', 'velit', 'finibus', 'viverra', 'maecenas', 'mattis', 'volutpat', 'justo', 'vitae', 'vestibulum', 'metus', 'lobortis', 'mauris', 'luctus', 'leo', 'feugiat', 'nibh', 'tincidunt', 'a', 'integer', 'facilisis', 'lacinia', 'ligula', 'ac', 'suspendisse', 'eleifend', 'nunc', 'nec', 'pulvinar', 'quisque', 'ut', 'semper', 'auctor', 'tortor', 'mollis', 'est', 'tempor', 'scelerisque', 'venenatis', 'quis', 'ultrices', 'tellus', 'nisi', 'phasellus', 'aliquam', 'molestie', 'purus', 'convallis', 'cursus', 'ex', 'massa', 'fusce', 'felis', 'fringilla', 'faucibus', 'varius', 'ante', 'primis', 'orci', 'et', 'posuere', 'cubilia', 'curae', 'proin', 'ultricies', 'hendrerit', 'ornare', 'augue', 'pharetra', 'dapibus', 'nullam', 'sollicitudin', 'euismod', 'eget', 'pretium', 'vulputate', 'urna', 'arcu', 'porttitor', 'quam', 'condimentum', 'consequat', 'tempus', 'hac', 'habitasse', 'platea', 'dictumst', 'sagittis', 'gravida', 'eu', 'commodo', 'dui', 'lectus', 'vivamus', 'libero', 'vel', 'maximus', 'pellentesque', 'efficitur', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra', 'inceptos', 'himenaeos', 'fermentum', 'turpis', 'donec', 'magna', 'porta', 'enim', 'curabitur', 'odio', 'rhoncus', 'blandit', 'potenti', 'sodales', 'accumsan', 'congue', 'neque', 'duis', 'bibendum', 'laoreet', 'elementum', 'suscipit', 'diam', 'vehicula', 'eros', 'nam', 'imperdiet', 'sem', 'ullamcorper', 'dignissim', 'risus', 'aliquet', 'habitant', 'morbi', 'tristique', 'senectus', 'netus', 'fames', 'nisl', 'iaculis', 'cras', 'aenean'];

	/**
	 * @param int $num_paragraphs
	 * @return string
	 */
	public static function ipsum($num_paragraphs)
	{
		$paragraphs = [];
		for ($p = 0; $p < $num_paragraphs; ++$p) {
			$num_sentences = random_int(3, 8);
			$sentences = [];

			for ($s = 0; $s < $num_sentences; ++$s) {
				$frags = [];
				$comma_chance = .33;

				while (true) {
					$num_words = random_int(3, 15);
					$words = self::random_values(self::$lorem, $num_words);
					$frags[] = implode(' ', $words);

					if (self::random_float() >= $comma_chance) {
						break;
					}

					$comma_chance /= 2;
				}

				$sentences[] = ucfirst(implode(', ', $frags)) . '.';
			}

			$paragraphs[] = implode(' ', $sentences);
		}

		return implode("\n\n", $paragraphs);
	}

	/**
	 * @return float
	 */
	private static function random_float()
	{
		return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
	}

	/**
	 * @param array $arr
	 * @param int $count
	 * @return array
	 */
	private static function random_values($arr, $count)
	{
		$keys = array_rand($arr, $count);
		if ($count == 1) {
			$keys = [$keys];
		}

		return array_intersect_key($arr, array_fill_keys($keys, null));
	}
}

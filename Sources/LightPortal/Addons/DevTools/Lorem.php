<?php

/**
 * Lorem.php
 *
 * @package DevTools (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.12.21
 */

namespace Bugo\LightPortal\Addons\DevTools;

/**
 * https://stackoverflow.com/a/39986034/14091866
 */
abstract class Lorem
{
	private const WORDS = [
		'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'praesent', 'interdum', 'dictum',
		'mi', 'non', 'egestas', 'nulla', 'in', 'lacus', 'sed', 'sapien', 'placerat', 'malesuada', 'at', 'erat', 'etiam',
		'id', 'velit', 'finibus', 'viverra', 'maecenas', 'mattis', 'volutpat', 'justo', 'vitae', 'vestibulum', 'metus',
		'lobortis', 'mauris', 'luctus', 'leo', 'feugiat', 'nibh', 'tincidunt', 'a', 'integer', 'facilisis', 'lacinia',
		'ligula', 'ac', 'suspendisse', 'eleifend', 'nunc', 'nec', 'pulvinar', 'quisque', 'ut', 'semper', 'auctor',
		'tortor', 'mollis', 'est', 'tempor', 'scelerisque', 'venenatis', 'quis', 'ultrices', 'tellus', 'nisi',
		'phasellus', 'aliquam', 'molestie', 'purus', 'convallis', 'cursus', 'ex', 'massa', 'fusce', 'felis',
		'fringilla', 'faucibus', 'varius', 'ante', 'primis', 'orci', 'et', 'posuere', 'cubilia', 'curae', 'proin',
		'ultricies', 'hendrerit', 'ornare', 'augue', 'pharetra', 'dapibus', 'nullam', 'sollicitudin', 'euismod',
		'eget', 'pretium', 'vulputate', 'urna', 'arcu', 'porttitor', 'quam', 'condimentum', 'consequat', 'tempus',
		'hac', 'habitasse', 'platea', 'dictumst', 'sagittis', 'gravida', 'eu', 'commodo', 'dui', 'lectus', 'vivamus',
		'libero', 'vel', 'maximus', 'pellentesque', 'efficitur', 'class', 'aptent', 'taciti', 'sociosqu', 'ad',
		'litora', 'torquent', 'per', 'conubia', 'nostra', 'inceptos', 'himenaeos', 'fermentum', 'turpis', 'donec',
		'magna', 'porta', 'enim', 'curabitur', 'odio', 'rhoncus', 'blandit', 'potenti', 'sodales', 'accumsan',
		'congue', 'neque', 'duis', 'bibendum', 'laoreet', 'elementum', 'suscipit', 'diam', 'vehicula', 'eros', 'nam',
		'imperdiet', 'sem', 'ullamcorper', 'dignissim', 'risus', 'aliquet', 'habitant', 'morbi', 'tristique',
		'senectus', 'netus', 'fames', 'nisl', 'iaculis', 'cras', 'aenean'
	];

	public static function ipsum(int $num_paragraphs): string
	{
		$paragraphs = [];
		for ($p = 0; $p < $num_paragraphs; ++$p) {
			$num_sentences = $this->smcFunc['random_int'](3, 8);
			$sentences = [];

			for ($s = 0; $s < $num_sentences; ++$s) {
				$frags = [];
				$comma_chance = .33;

				while (true) {
					$num_words = $this->smcFunc['random_int'](3, 15);
					$words = self::random_values(self::WORDS, $num_words);
					$frags[] = implode(' ', $words);

					if (($this->smcFunc['random_int'](0, PHP_INT_MAX - 1) / PHP_INT_MAX) >= $comma_chance) {
						break;
					}

					$comma_chance /= 2;
				}

				$sentences[] = ucfirst(implode(', ', $frags)) . '.';
			}

			$paragraphs[] = implode(' ', $sentences);
		}

		return implode(PHP_EOL . PHP_EOL, $paragraphs);
	}

	private static function random_values(array $arr, int $count): array
	{
		$keys = array_rand($arr, $count);
		if ($count === 1) {
			$keys = [$keys];
		}

		return array_intersect_key($arr, array_fill_keys($keys, null));
	}
}

<?php

/**
 * Especially for SMF 2.1.*, (C) 2020, Bugo
 */

class ParsedownSMF extends ParsedownExtended
{
	/**
	 * Добавляем классы таблице и строкам
	 *
	 * @param array $Line
	 * @param array $Block
	 * @return void
	 */
	protected function blockTableContinue($Line, array $Block)
	{
		if (isset($Block['interrupted']))
			return;

		$Block['element']['attributes']['class'] = 'table_grid';

		if (count($Block['alignments']) === 1 or $Line['text'][0] === '|' or strpos($Line['text'], '|')) {
			$Elements = array();

			$row = $Line['text'];

			$row = trim($row);
			$row = trim($row, '|');

			preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

			$cells = array_slice($matches[0], 0, count($Block['alignments']));

			foreach ($cells as $index => $cell) {
				$cell = trim($cell);

				$Element = array(
					'name' => 'td',
					'handler' => array(
						'function'    => 'lineElements',
						'argument'    => $cell,
						'destination' => 'elements'
					)
				);

				if (isset($Block['alignments'][$index])) {
					$Element['attributes'] = array(
						'style' => 'text-align: ' . $Block['alignments'][$index] . ';'
					);
				}

				$Elements[] = $Element;
			}

			$Element = array(
				'name'     => 'tr class="windowbg"',
				'elements' => $Elements
			);

			$Block['element']['elements'][1]['elements'][] = $Element;

			return $Block;
		}
	}
}
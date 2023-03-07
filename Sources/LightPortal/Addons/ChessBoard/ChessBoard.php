<?php

/**
 * ChessBoard.php
 *
 * @package ChessBoard (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 9.03.23
 */

namespace Bugo\LightPortal\Addons\ChessBoard;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class ChessBoard extends Plugin
{
	public string $type = 'block';

	public string $icon = 'fas fa-chess';

	public function prepareContent(string $type, int $block_id)
	{
		if ($type !== 'chess_board')
			return;

		$this->loadExtCSS('https://unpkg.com/@chrisoakman/chessboard2@0/dist/chessboard2.min.css');
		$this->loadExtJS('https://unpkg.com/@chrisoakman/chessboard2@0/dist/chessboard2.min.js');
		$this->loadExtJS('https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.12.1/chess.js');

		echo '
		<div id="chessBoard', $block_id, '"></div>
		<div class="floatright" style="margin: 10px">
			<label for="depth', $block_id, '">', $this->txt['lp_chess_board']['search_depth'], ':</label>
			<select id="depth', $block_id, '">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3" selected>3</option>
				<option value="4">4</option>
				<option value="5">5</option>
			</select>
		</div>
		<script>
			const gameOver = "', $this->txt['lp_chess_board']['game_over'], '";
			const board', $block_id, ' = new ChessboardMaker(', $block_id, ');
		</script>';
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'chessboard2 javascript library',
			'link' => 'https://github.com/oakmac/chessboard2',
			'author' => 'Chris Oakman',
			'license' => [
				'name' => 'the ISC License',
				'link' => 'https://github.com/oakmac/chessboard2/blob/master/LICENSE.md'
			]
		];

		$links[] = [
			'title' => 'chess.js',
			'link' => 'https://github.com/jhlywa/chess.js',
			'author' => 'Jeff Hlywa',
			'license' => [
				'name' => 'the BSD 2-Clause "Simplified" License',
				'link' => 'https://github.com/jhlywa/chess.js/blob/master/LICENSE'
			]
		];
	}
}

<?php

/**
 * @package ChessBoard (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\ChessBoard;

use Bugo\Compat\Theme;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

class ChessBoard extends Block
{
	public string $type = 'block';

	public string $icon = 'fas fa-chess';

	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		Theme::loadCSSFile('https://unpkg.com/@chrisoakman/chessboard2@0/dist/chessboard2.min.css', ['external' => true]);
		Theme::loadJavaScriptFile('https://unpkg.com/@chrisoakman/chessboard2@0/dist/chessboard2.min.js', ['external' => true]);
		Theme::loadJavaScriptFile('https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.12.1/chess.js', ['external' => true]);

		$id = $e->args->id;

		echo /** @lang text */ '
		<div id="chessBoard' . $id . '"></div>
		<div class="floatright" style="margin: 10px">
			<label for="depth' . $id . '">' . $this->txt['search_depth'] . ':</label>
			<select id="depth' . $id . '">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3" selected>3</option>
				<option value="4">4</option>
				<option value="5">5</option>
			</select>
		</div>
		<script>
			const gameOver = "' . $this->txt['game_over'] . '";
			const board' . $id . ' = new ChessboardMaker(' . $id . ');
		</script>';
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'chessboard2 javascript library',
			'link' => 'https://github.com/oakmac/chessboard2',
			'author' => 'Chris Oakman',
			'license' => [
				'name' => 'the ISC License',
				'link' => 'https://github.com/oakmac/chessboard2/blob/master/LICENSE.md'
			]
		];

		$e->args->links[] = [
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

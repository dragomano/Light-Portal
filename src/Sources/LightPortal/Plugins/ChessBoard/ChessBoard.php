<?php declare(strict_types=1);

/**
 * @package ChessBoard (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.03.25
 */

namespace Bugo\LightPortal\Plugins\ChessBoard;

use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;

if (! defined('LP_NAME'))
	die('No direct access...');

class ChessBoard extends Block
{
	public string $type = 'block games';

	public string $icon = 'fas fa-chess';

	public function prepareContent(Event $e): void
	{
		$this->loadExternalResources([
			['type' => 'css', 'url' => 'https://cdn.jsdelivr.net/npm/@chrisoakman/chessboard2@0/dist/chessboard2.min.css'],
			['type' => 'js', 'url' => 'https://cdn.jsdelivr.net/npm/@chrisoakman/chessboard2@0/dist/chessboard2.min.js'],
			['type' => 'js', 'url' => 'https://cdn.jsdelivr.net/npm/chess.js@0.12.1/chess.min.js'],
		]);

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
			const board' . $id . ' = new ChessboardMaker(' . $id . ', "' . $this->txt['game_over'] . '");
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

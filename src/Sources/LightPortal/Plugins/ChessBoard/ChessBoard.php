<?php declare(strict_types=1);

/**
 * @package ChessBoard (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 04.12.25
 */

namespace LightPortal\Plugins\ChessBoard;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\AssetBuilder;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\GameBlock;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\RadioField;
use LightPortal\UI\Fields\RangeField;
use LightPortal\UI\Fields\SelectField;
use LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-chess')]
class ChessBoard extends GameBlock
{
	const ENGINE = 'stockfish-17.1-lite-single-03e3232';

	const VERSION = '17.1';

	const BOARD_STYLE = [
		'default'    => 'default',
		'green'      => 'green',
		'blue'       => 'blue',
		'chess-club' => 'chess-club',
	];

	const PIECE_STYLE = [
		'standard' => 'standard',
		'staunty'  => 'staunty',
	];

	const BORDER_TYPE = [
		'thin'  => 'thin',
		'frame' => 'frame',
		'none'  => 'none',
	];

	const MARKER_TYPE = [
		'frame'              => 'frame',
		'framePrimary'       => 'framePrimary',
		'frameDanger'        => 'frameDanger',
		'circle'             => 'circle',
		'circlePrimary'      => 'circlePrimary',
		'circleDanger'       => 'circleDanger',
		'circleDangerFilled' => 'circleDangerFilled',
		'square'             => 'square',
		'dot'                => 'dot',
		'bevel'              => 'bevel',
	];

	public function init(): void
	{
		$link = Str::html('a')
			->href('https://github.com/nmrugg/stockfish.js')
			->target('_blank')
			->rel('noopener')
			->addText('Stockfish ' . self::VERSION);

		Lang::$txt['lp_chess_board']['description'] .= ' ' . sprintf(
			Lang::$txt['lp_chess_board']['engine_note'],
			$link
		);
	}

	public function prepareAssets(Event $e): void
	{
		$builder = new AssetBuilder($this);

		$builder->scripts()
			->add('chessboard.js')
			->add('stockfish/' . self::ENGINE . '.js')
			->add('stockfish/' . self::ENGINE . '.wasm');

		$builder->css()->add('chessboard.css');

		$builder->images()
			->addMultiple([
				'images/standard.svg',
				'images/staunty.svg',
				'images/markers.svg',
			]);

		$builder->appendTo($e->args->assets);
	}

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'board_style' => self::BOARD_STYLE['default'],
			'piece_style' => self::PIECE_STYLE['standard'],
			'border_type' => self::BORDER_TYPE['frame'],
			'marker_type' => self::MARKER_TYPE['frame'],
			'skill_level' => 10,
			'depth'       => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'board_style'  => FILTER_DEFAULT,
			'piece_style'  => FILTER_DEFAULT,
			'border_type'  => FILTER_DEFAULT,
			'marker_type'  => FILTER_DEFAULT,
			'skill_level'  => FILTER_VALIDATE_INT,
			'depth'        => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		SelectField::make('board_style', $this->txt['board_style'])
			->setTab(Tab::APPEARANCE)
			->setOptions(self::BOARD_STYLE)
			->setValue($options['board_style']);

		RadioField::make('piece_style', $this->txt['piece_style'])
			->setTab(Tab::APPEARANCE)
			->setOptions(self::PIECE_STYLE)
			->setValue($options['piece_style']);

		SelectField::make('border_type', $this->txt['border_type'])
			->setTab(Tab::APPEARANCE)
			->setOptions(self::BORDER_TYPE)
			->setValue($options['border_type']);

		SelectField::make('marker_type', $this->txt['marker_type'])
			->setTab(Tab::APPEARANCE)
			->setOptions(self::MARKER_TYPE)
			->setValue($options['marker_type']);

		RangeField::make('skill_level', $this->txt['skill_level'])
			->setAttribute('min', 0)
			->setAttribute('max', 20)
			->setValue($options['skill_level']);

		RangeField::make('depth', $this->txt['depth'])
			->setAttribute('min', 1)
			->setAttribute('max', 25)
			->setValue($options['depth']);
	}

	public function prepareContent(Event $e): void
	{
		Theme::loadCSSFile('light_portal/chess_board/chessboard.css');

		$this->handleApiRequest($e);

		$apiData = $this->getApiData($e);

		echo /** @lang text */ '
		<div
			class="chess_board"
			id="chess_board_' . $e->args->id . '"
			data-api-data="' . htmlspecialchars(json_encode($apiData), ENT_QUOTES) . '"
		></div>
		<script type="module">
		    window.portalApiData = window.portalApiData || {};
		    window.portalApiData["chess_board_' . $e->args->id . '"] = "' . $e->args->id . '";
		    usePortalApi("' . $this->buildApiUrl($e) . '", "chess_board/chessboard.js")
		</script>';
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title'   => 'chess.js',
			'link'    => 'https://github.com/jhlywa/chess.js',
			'author'  => 'Jeff Hlywa',
			'license' => [
				'name' => 'the BSD 2-Clause "Simplified" License',
				'link' => 'https://github.com/jhlywa/chess.js/blob/master/LICENSE'
			]
		];

		$e->args->links[] = [
			'title'   => 'cm-chessboard',
			'link'    => 'https://github.com/shaack/cm-chessboard',
			'author'  => 'Stefan Haack',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/shaack/cm-chessboard#MIT-1-ov-file'
			]
		];

		$e->args->links[] = [
			'title'   => 'Stockfish.js',
			'link'    => 'https://github.com/nmrugg/stockfish.js',
			'author'  => 'Chess.com',
			'license' => [
				'name' => 'the GPL-3.0 License',
				'link' => 'https://github.com/nmrugg/stockfish.js#GPL-3.0-1-ov-file'
			]
		];
	}

	protected function getApiData(Event $e): array
	{
		$scripts = Theme::$current->settings['default_theme_url'] . '/scripts/light_portal';
		$images  = Theme::$current->settings['default_theme_url'] . '/images/light_portal';

		$parameters = $e->args->parameters;

		return [
			'txt'     => $this->txt,
			'context' => [
				'id'         => $e->args->id,
				'engineUrl'  => $scripts . '/chess_board/' . self::ENGINE . '.js',
				'assetsUrl'  => $images . '/chess_board/',
				'boardStyle' => $parameters['board_style'],
				'pieceStyle' => $parameters['piece_style'],
				'borderType' => $parameters['border_type'],
				'markerType' => $parameters['marker_type'],
				'skillLevel' => $parameters['skill_level'],
				'depth'      => $parameters['depth'],
				'locale'     => Lang::$txt['lang_dictionary'],
			],
		];
	}
}

<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { Chess } from 'chess.js';
  import { INPUT_EVENT_TYPE, COLOR, BORDER_TYPE, Chessboard } from 'cm-chessboard/src/Chessboard';
  import { MARKER_TYPE, Markers } from 'cm-chessboard/src/extensions/markers/Markers';
  import 'cm-chessboard/assets/chessboard.css';
  import 'cm-chessboard/assets/extensions/markers/markers.css';

  type InputEventType = typeof INPUT_EVENT_TYPE[keyof typeof INPUT_EVENT_TYPE];

  interface MoveInputEvent {
    type: InputEventType;
    chessboard?: any;
    square?: string;
    squareFrom?: string;
    squareTo?: string;
  }

  let {
    id,
    engineUrl,
    assetsUrl,
    boardStyle,
    pieceStyle,
    borderType,
    markerType,
    skillLevel,
    depth
  } = $props();

  const createStockfish = function () {
    const worker = new Worker(`${engineUrl}`);

    worker.onmessage = (e) => console.log('[Stockfish]', e.data);
    worker.postMessage('uci');

    return worker;
  };

  let boardEl: HTMLDivElement = $state();
  let game = $state(null);
  let board = $state(null);
  let stockfish = $state(null);
  let isThinking = $state(false);
  let playerColor = $state(COLOR.white);
  let computerColor = $state(COLOR.black);
  let status = $state('');
  let playersInfo = $state('');

  $effect(() => {
    const initTimeout = setTimeout(() => {
      if (boardEl && typeof Chessboard !== 'undefined') {
        startGame();
      }
    }, 100);

    return () => {
      clearTimeout(initTimeout);

      if (stockfish) {
        stockfish.postMessage('stop');
        stockfish.terminate?.();
      }

      if (board) {
        board.destroy();
      }
    };
  });

  function startGame() {
    game = new Chess();
    isThinking = false;

    if (stockfish) stockfish.postMessage('stop');

    playerColor = Math.random() < 0.5 ? COLOR.white : COLOR.black;
    computerColor = playerColor === COLOR.white ? COLOR.black : COLOR.white;

    if (board) {
      board.destroy();
    }

    updatePlayersInfo();

    board = new Chessboard(boardEl, {
      position: game.fen(),
      orientation: playerColor,
      assetsUrl: assetsUrl,
      style: {
        cssClass: boardStyle,
        showCoordinates: true,
        borderType: BORDER_TYPE[borderType],
        pieces: { file: `${pieceStyle}.svg` },
        animationDuration: 300
      },
      extensions: [
        { class: Markers, props: { autoMarkers: MARKER_TYPE[markerType], sprite: 'markers.svg' } }
      ]
    });

    board.enableMoveInput(handleMoveInput, playerColor);

    updateStatus();

    if (!stockfish) {
      initStockfish();
    }

    if (computerColor === COLOR.white) {
      setTimeout(makeComputerMove, 1000);
    }
  }

  function handleMoveInput(event: MoveInputEvent) {
    if (isThinking) return false;

    const currentBoard = event.chessboard || board;

    switch (event.type) {
      case INPUT_EVENT_TYPE.moveInputStarted:
        if (game.isGameOver()) return false;

        const piece = game.get(event.square);

        if (!piece || piece.color !== playerColor) return false;

        const moves = game.moves({
          square: event.square,
          verbose: true
        });

        if (currentBoard.addLegalMovesMarkers) {
          currentBoard.addLegalMovesMarkers(moves);
        }

        return moves.length > 0;

      case INPUT_EVENT_TYPE.validateMoveInput:
        const possibleMoves = game.moves({
          square: event.squareFrom,
          verbose: true
        });

        const isValidMove = possibleMoves.some((move: any) => move.to === event.squareTo);

        if (!isValidMove) {
          return false;
        }

        const move = game.move({
          from: event.squareFrom,
          to: event.squareTo,
          promotion: 'q'
        });

        if (move === null) {
          return false;
        }

        board.setPosition(game.fen(), true);

        updateStatus();

        if (!game.isGameOver() && game.turn() === computerColor) {
          setTimeout(makeComputerMove, 500);
        }

        return true;

      case INPUT_EVENT_TYPE.moveInputFinished:
      case INPUT_EVENT_TYPE.moveInputCanceled:
        if (currentBoard.removeLegalMovesMarkers) {
          currentBoard.removeLegalMovesMarkers();
        }

        return true;

      default:
        return true;
    }
  }

  async function initStockfish() {
    stockfish = createStockfish();
    stockfish.postMessage('uci');
    stockfish.postMessage('set' + `option name Skill Level value ${skillLevel}`);

    stockfish.onmessage = (msg: MessageEvent) => {
      const text = msg.data;

      if (text.startsWith('best' + 'move')) {
        const bestMove = text.split(' ')[1];

        if (
          bestMove &&
          bestMove !== 'null' &&
          !game.isGameOver() &&
          isThinking &&
          game.turn() === computerColor
        ) {
          const move = game.move(bestMove, { sloppy: true });

          if (move) {
            board.setPosition(game.fen(), true);

            updateStatus();
          }
        }

        isThinking = false;
      }

      if (text.includes('error') || text.includes('unknown')) {
        isThinking = false;

        console.error('Stockfish error:', text);
      }
    };
  }

  function makeComputerMove() {
    if (game.isGameOver() || isThinking || game.turn() !== computerColor) return;

    isThinking = true;

    status = $_('thinking');

    stockfish.postMessage(`position fen ${game.fen()}`);
    stockfish.postMessage(`go depth ${depth}`);

    setTimeout(() => {
      if (isThinking) {
        isThinking = false;

        updateStatus();
      }
    }, 5000);
  }

  function updateStatus() {
    if (game.isGameOver()) {
      if (game.isCheckmate()) {
        const winner = game.turn() === COLOR.white ? $_('black_won') : $_('white_won');

        status = $_('checkmate') + ` ${winner}`;
      } else if (game.isDraw()) {
        status = $_('game_draw');
      } else {
        status = $_('game_over');
      }
    } else {
      const turn = game.turn() === COLOR.white ? 'white_turn' : 'black_turn';

      status = $_(turn);

      if (game.isCheck()) status += $_('check');
    }
  }

  function updatePlayersInfo() {
    if (playerColor === COLOR.white) {
      playersInfo = 'player_white';
    } else {
      playersInfo = 'player_black';
    }
  }
</script>

<div data-chess-board={id} class="chess_board_container">
  <div bind:this={boardEl} class="board"></div>
  <div class="status">{status}</div>
  <button class="button" onclick={startGame}>{$_('new_game')}</button>
  <div class="info">
    <p>{$_(playersInfo)}</p>
  </div>
</div>

<style lang="scss">
  .chess_board_container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 20px;

    .board {
      width: 90%;
      margin: 20px;
      border: 1px solid #333;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .status {
      margin-top: 20px;
      font-size: 1.2em;
      font-weight: bold;
      color: #333;
    }

    button {
      margin-top: 10px;
      padding: 5px 10px;
      height: auto !important;
      font-size: 1em;
      cursor: pointer;
      background: #4caf50;
      color: white;
      border: none;
      border-radius: 4px;
    }

    button:hover {
      background: #45a049;
    }

    .info {
      margin-top: 15px;
      font-size: 0.9em;
      color: #666;
      text-align: center;
    }
  }
</style>

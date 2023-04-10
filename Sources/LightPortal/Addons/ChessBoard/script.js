class ChessboardMaker {
	constructor(block_id) {
		this.id = block_id
		this.game = new Chess()

		this.minimaxRoot = this.minimaxRoot.bind(this)
		this.minimax = this.minimax.bind(this)
		this.evaluateBoard = this.evaluateBoard.bind(this)
		this.getPieceValue = this.getPieceValue.bind(this)
		this.getAbsoluteValue = this.getAbsoluteValue.bind(this)
		this.reverseArray = this.reverseArray.bind(this)
		this.makeBestMove = this.makeBestMove.bind(this)
		this.getBestMove = this.getBestMove.bind(this)
		this.onDragStart = this.onDragStart.bind(this)
		this.onTouchSquare = this.onTouchSquare.bind(this)
		this.onDrop = this.onDrop.bind(this)
		this.onSnapEnd = this.onSnapEnd.bind(this)

		this.config = {
			draggable : true,
			position : 'start',
			onDragStart : this.onDragStart,
			onTouchSquare: this.onTouchSquare,
			onDrop : this.onDrop,
			onSnapEnd : this.onSnapEnd
		}

		this.board = Chessboard2('chessBoard' + block_id, this.config)

		this.pendingMove = null
	}

	minimaxRoot(depth, isMaximisingPlayer) {
		const newGameMoves = this.game.moves()

		let bestMove = -9999
		let bestMoveFound

		for(let i = 0; i < newGameMoves.length; i++) {
			const newGameMove = newGameMoves[i]

			this.game.move(newGameMove)

			const value = this.minimax(depth - 1, -10000, 10000, ! isMaximisingPlayer)

			this.game.undo()

			if (value >= bestMove) {
				bestMove = value
				bestMoveFound = newGameMove
			}
		}

		return bestMoveFound
	}

	minimax(depth, alpha, beta, isMaximisingPlayer) {
		if (depth === 0) {
			return -this.evaluateBoard(this.game.board())
		}

		const newGameMoves = this.game.moves()

		if (isMaximisingPlayer) {
			let bestMove = -9999

			for (let i = 0; i < newGameMoves.length; i++) {
				this.game.move(newGameMoves[i])
				bestMove = Math.max(bestMove, this.minimax(depth - 1, alpha, beta, ! isMaximisingPlayer))
				this.game.undo()
				alpha = Math.max(alpha, bestMove)

				if (beta <= alpha) {
					return bestMove
				}
			}

			return bestMove
		} else {
			let bestMove = 9999

			for (let i = 0; i < newGameMoves.length; i++) {
				this.game.move(newGameMoves[i])
				bestMove = Math.min(bestMove, this.minimax(depth - 1, alpha, beta, ! isMaximisingPlayer))
				this.game.undo()
				beta = Math.min(beta, bestMove)

				if (beta <= alpha) {
					return bestMove
				}
			}

			return bestMove
		}
	}

	evaluateBoard(board) {
		let totalEvaluation = 0

		for (let i = 0; i < 8; i++) {
			for (let j = 0; j < 8; j++) {
				totalEvaluation = totalEvaluation + this.getPieceValue(board[i][j], i ,j)
			}
		}

		return totalEvaluation
	}

	reverseArray(array) {
		return array.slice().reverse()
	}

	getAbsoluteValue(piece, isWhite, x, y) {
		const pawnEvalWhite = [
			[0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
			[5.0, 5.0, 5.0, 5.0, 5.0, 5.0, 5.0, 5.0],
			[1.0, 1.0, 2.0, 3.0, 3.0, 2.0, 1.0, 1.0],
			[0.5, 0.5, 1.0, 2.5, 2.5, 1.0, 0.5, 0.5],
			[0.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0],
			[0.5, -0.5, -1.0, 0.0, 0.0, -1.0, -0.5, 0.5],
			[0.5, 1.0, 1.0, -2.0, -2.0, 1.0, 1.0, 0.5],
			[0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]
		]

		const pawnEvalBlack = this.reverseArray(pawnEvalWhite)

		const knightEval = [
			[-5.0, -4.0, -3.0, -3.0, -3.0, -3.0, -4.0, -5.0],
			[-4.0, -2.0, 0.0, 0.0, 0.0, 0.0, -2.0, -4.0],
			[-3.0, 0.0, 1.0, 1.5, 1.5, 1.0, 0.0, -3.0],
			[-3.0, 0.5, 1.5, 2.0, 2.0, 1.5, 0.5, -3.0],
			[-3.0, 0.0, 1.5, 2.0, 2.0, 1.5, 0.0, -3.0],
			[-3.0, 0.5, 1.0, 1.5, 1.5, 1.0, 0.5, -3.0],
			[-4.0, -2.0, 0.0, 0.5, 0.5, 0.0, -2.0, -4.0],
			[-5.0, -4.0, -3.0, -3.0, -3.0, -3.0, -4.0, -5.0]
		]

		const bishopEvalWhite = [
			[ -2.0, -1.0, -1.0, -1.0, -1.0, -1.0, -1.0, -2.0],
			[ -1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -1.0],
			[ -1.0, 0.0, 0.5, 1.0, 1.0, 0.5, 0.0, -1.0],
			[ -1.0, 0.5, 0.5, 1.0, 1.0, 0.5, 0.5, -1.0],
			[ -1.0, 0.0, 1.0, 1.0, 1.0, 1.0, 0.0, -1.0],
			[ -1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, -1.0],
			[ -1.0, 0.5, 0.0, 0.0, 0.0, 0.0, 0.5, -1.0],
			[ -2.0, -1.0, -1.0, -1.0, -1.0, -1.0, -1.0, -2.0]
		]

		const bishopEvalBlack = this.reverseArray(bishopEvalWhite)

		const rookEvalWhite = [
			[0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
			[0.5, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 0.5],
			[-0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -0.5],
			[-0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -0.5],
			[-0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -0.5],
			[-0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -0.5],
			[-0.5, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -0.5],
			[0.0, 0.0, 0.0, 0.5, 0.5, 0.0, 0.0, 0.0]
		]

		const rookEvalBlack = this.reverseArray(rookEvalWhite)

		const evalQueen = [
			[-2.0, -1.0, -1.0, -0.5, -0.5, -1.0, -1.0, -2.0],
			[-1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, -1.0],
			[-1.0, 0.0, 0.5, 0.5, 0.5, 0.5, 0.0, -1.0],
			[-0.5, 0.0, 0.5, 0.5, 0.5, 0.5, 0.0, -0.5],
			[0.0, 0.0, 0.5, 0.5, 0.5, 0.5, 0.0, -0.5],
			[-1.0, 0.5, 0.5, 0.5, 0.5, 0.5, 0.0, -1.0],
			[-1.0, 0.0, 0.5, 0.0, 0.0, 0.0, 0.0, -1.0],
			[-2.0, -1.0, -1.0, -0.5, -0.5, -1.0, -1.0, -2.0]
		]

		const kingEvalWhite = [
			[-3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
			[-3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
			[-3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
			[-3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
			[-2.0, -3.0, -3.0, -4.0, -4.0, -3.0, -3.0, -2.0],
			[-1.0, -2.0, -2.0, -2.0, -2.0, -2.0, -2.0, -1.0],
			[2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 2.0],
			[2.0, 3.0, 1.0, 0.0, 0.0, 1.0, 3.0, 2.0]
		]

		const kingEvalBlack = this.reverseArray(kingEvalWhite)

		if (piece.type === 'p') {
			return 10 + ( isWhite ? pawnEvalWhite[y][x] : pawnEvalBlack[y][x] )
		} else if (piece.type === 'r') {
			return 50 + ( isWhite ? rookEvalWhite[y][x] : rookEvalBlack[y][x] )
		} else if (piece.type === 'n') {
			return 30 + knightEval[y][x]
		} else if (piece.type === 'b') {
			return 30 + ( isWhite ? bishopEvalWhite[y][x] : bishopEvalBlack[y][x] )
		} else if (piece.type === 'q') {
			return 90 + evalQueen[y][x]
		} else if (piece.type === 'k') {
			return 900 + ( isWhite ? kingEvalWhite[y][x] : kingEvalBlack[y][x] )
		}

		throw "Unknown piece type: " + piece.type
	}

	getPieceValue(piece, x, y) {
		if (piece === null) {
			return 0
		}

		const absoluteValue = this.getAbsoluteValue(piece, piece.color === 'w', x ,y)

		return piece.color === 'w' ? absoluteValue : -absoluteValue
	}

	onDragStart(dragStartEvt) {
		if (this.game.in_checkmate() === true || this.game.in_draw() === true || dragStartEvt.piece.search(/^b/) !== -1) {
			return false
		}
	}

	onTouchSquare(square, piece) {
		const legalMoves = this.game.moves({ square, verbose: true })

		if (! this.pendingMove && legalMoves.length > 0) {
			this.pendingMove = square

			legalMoves.forEach(move => this.board.addCircle(move.to))
		} else if (this.pendingMove && this.pendingMove === square) {
			this.pendingMove = null
			this.board.clearCircles()
		} else if (this.pendingMove) {
			const moveResult = this.game.move({
				from: this.pendingMove,
				to: square,
				promotion: 'q'
			})

			if (moveResult) {
				this.board.clearCircles()
				this.board.position(this.game.fen()).then(() => window.setTimeout(this.makeBestMove, 250))
			} else if (piece) {
				this.pendingMove = square
				this.board.clearCircles()

				legalMoves.forEach(m => this.board.addCircle(m.to))
			} else {
				this.pendingMove = null
				this.board.clearCircles()
			}
		}
	}

	makeBestMove() {
		this.game.move(this.getBestMove())
		this.board.position(this.game.fen())
	}

	getBestMove() {
		if (this.game.game_over()) {
			alert(gameOver)

			return this.game.reset()
		}

		const depth = document.getElementById("depth" + this.id).value;

		return this.minimaxRoot(depth, true)
	}

	onDrop(dropEvt) {
		const move = this.game.move({
			from: dropEvt.source,
			to: dropEvt.target,
			promotion: 'q'
		})

		this.board.clearCircles()
		this.pendingMove = null

		if (move === null) {
			return 'snapback'
		}

		window.setTimeout(this.makeBestMove, 250)
	}

	onSnapEnd() {
		this.board.position(this.game.fen())
	}
}

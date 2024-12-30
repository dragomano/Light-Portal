document.addEventListener('DOMContentLoaded', () => {
  const containers = document.querySelectorAll('.puzzle-container')
  const size = 4

  containers.forEach(container => {
    const grid = container.querySelector('.grid')
    let board = Array.from({ length: size }, () => Array(size).fill(0))

    function startGame() {
      addRandomTile()
      addRandomTile()
      updateBoard()
    }

    function addRandomTile() {
      const emptyCells = []

      for (let i = 0; i < size; i++) {
        for (let j = 0; j < size; j++) {
          if (board[i][j] === 0) {
            emptyCells.push({ x: i, y: j })
          }
        }
      }

      if (emptyCells.length > 0) {
        const randomCell = emptyCells[Math.floor(Math.random() * emptyCells.length)]
        board[randomCell.x][randomCell.y] = Math.random() < 0.9 ? 2 : 4
      }
    }

    function updateBoard() {
      grid.innerHTML = ''

      for (let i = 0; i < size; i++) {
        for (let j = 0; j < size; j++) {
          const cell = document.createElement('div')

          cell.classList.add('cell')

          if (board[i][j] !== 0) {
            cell.classList.add(`cell-${board[i][j]}`)
            cell.textContent = board[i][j]
          }

          grid.appendChild(cell)
        }
      }
    }

    function move(direction) {
      let moved = false

      if (direction === 'left' || direction === 'right') {
        for (let i = 0; i < size; i++) {
          let row = board[i].filter(num => num !== 0)

          if (direction === 'right') row.reverse()

          let mergedRow = merge(row)

          if (direction === 'right') mergedRow.reverse()

          while (mergedRow.length < size) mergedRow.push(0)

          if (JSON.stringify(board[i]) !== JSON.stringify(mergedRow)) {
            moved = true
          }

          board[i] = mergedRow
        }
      } else if (direction === 'up' || direction === 'down') {
        for (let j = 0; j < size; j++) {
          let col = []

          for (let i = 0; i < size; i++) {
            if (board[i][j] !== 0) col.push(board[i][j])
          }

          if (direction === 'down') col.reverse()

          let mergedCol = merge(col)

          if (direction === 'down') mergedCol.reverse()

          while (mergedCol.length < size) mergedCol.push(0)

          for (let i = 0; i < size; i++) {
            if (board[i][j] !== mergedCol[i]) {
              moved = true
            }

            board[i][j] = mergedCol[i]
          }
        }
      }

      if (moved) {
        addRandomTile()
        updateBoard()

        if (isGameOver()) {
          alert('Game Over!')
        }
      }
    }

    function merge(arr) {
      const merged = []
      let skip = false

      for (let i = 0; i < arr.length; i++) {
        if (skip) {
          skip = false
          continue
        }

        if (arr[i] !== 0 && arr[i] === arr[i + 1]) {
          merged.push(arr[i] * 2)
          skip = true
        } else if (arr[i] !== 0) {
          merged.push(arr[i])
        }
      }

      while (merged.length < size) {
        merged.push(0)
      }

      return merged
    }

    function isGameOver() {
      for (let i = 0; i < size; i++) {
        for (let j = 0; j < size; j++) {
          if (board[i][j] === 0) return false

          if (i < size - 1 && board[i][j] === board[i + 1][j]) return false
          if (j < size - 1 && board[i][j] === board[i][j + 1]) return false
        }
      }

      return true
    }

    const controls = container.querySelectorAll('.button-control')

    controls.forEach(button => {
      button.addEventListener('click', () => {
        const direction = button.dataset.direction

        move(direction)
      })
    })

    startGame()
  })
})

class SudokuGame {
  constructor(container, langs, difficulty) {
      this.container = container;
      this.langs = langs;
      this.difficulty = difficulty;
      this.solution = this.generateBoard();
      this.puzzle = this.createPuzzle(this.solution, this.difficulty);
      this.grid = [];
      this.init();
  }

  init() {
      this.createGrid();
      this.fillPuzzle();
      this.createButtonRow();
  }

  createGrid() {
      const gridContainer = document.createElement('div');

      gridContainer.className = 'sudoku-grid';

      for (let row = 0; row < 9; row++) {
          this.grid[row] = [];

          for (let col = 0; col < 9; col++) {
              const cell = document.createElement('input');

              cell.type = 'text';
              cell.className = 'sudoku-cell';
              cell.dataset.row = String(row);
              cell.dataset.col = String(col);
              cell.maxLength = 1;
              cell.addEventListener('input', this.onCellInput.bind(this));
              gridContainer.appendChild(cell);

              this.grid[row][col] = cell;
          }
      }

      this.container.appendChild(gridContainer);
  }

  fillPuzzle() {
      for (let row = 0; row < 9; row++) {
          for (let col = 0; col < 9; col++) {
              if (this.puzzle[row][col] !== 0) {
                  this.grid[row][col].value = this.puzzle[row][col];
                  this.grid[row][col].disabled = true;
              } else {
                  this.grid[row][col].value = '';
                  this.grid[row][col].disabled = false;
              }
          }
      }
  }

  onCellInput(event) {
      const cell = event.target;
      const value = cell.value;

      if (!/^[1-9]$/.test(value)) {
          cell.value = '';
      }
  }

  checkSolution() {
      for (let row = 0; row < 9; row++) {
          for (let col = 0; col < 9; col++) {
              const cellValue = this.grid[row][col].value;

              if (cellValue !== String(this.solution[row][col])) {
                  return false;
              }
          }
      }

      return true;
  }

  createButtonRow() {
    this.row = document.createElement('div');
    this.row.className = 'row center-xs';
    this.createNewGameButton();
    this.createCheckButton();
    this.container.appendChild(this.row);
  }

  createCheckButton() {
      const button = document.createElement('button');

      button.textContent = this.langs.checkButton;
      button.className = 'button';
      button.addEventListener('click', () => {
          if (this.checkSolution()) {
              alert(this.langs.youWon);
          } else {
              alert(this.langs.errors);
          }
      });

      this.row.appendChild(button);
  }

  createNewGameButton() {
      const button = document.createElement('button');

      button.textContent = this.langs.newGame;
      button.className = 'button';
      button.addEventListener('click', () => {
          this.solution = this.generateBoard();
          this.puzzle = this.createPuzzle(this.solution, this.difficulty);
          this.fillPuzzle();
      });

      this.row.appendChild(button);
  }

  createPuzzle(board, difficulty = 40) {
    const puzzle = board.map(row => [...row]);

    let removed = 0;

    while (removed < difficulty) {
      const row = Math.floor(Math.random() * 9);
      const col = Math.floor(Math.random() * 9);

      if (puzzle[row][col] !== 0) {
        puzzle[row][col] = 0;
        removed++;
      }
    }

    return puzzle;
  }

  generateBoard() {
    const board = Array.from({ length: 9 }, () => Array(9).fill(0));

    function isValid(board, row, col, num) {
      if (board[row].includes(num)) return false;

      if (board.some(r => r[col] === num)) return false;

      const boxRow = Math.floor(row / 3) * 3;
      const boxCol = Math.floor(col / 3) * 3;

      for (let r = boxRow; r < boxRow + 3; r++) {
        for (let c = boxCol; c < boxCol + 3; c++) {
          if (board[r][c] === num) return false;
        }
      }

      return true;
    }

    function shuffle(array) {
      for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));

        [array[i], array[j]] = [array[j], array[i]];
      }

      return array;
    }

    function solve(board) {
      for (let row = 0; row < 9; row++) {
        for (let col = 0; col < 9; col++) {
          if (board[row][col] === 0) {
            const numbers = shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9]);

            for (const num of numbers) {
              if (isValid(board, row, col, num)) {
                board[row][col] = num;

                if (solve(board)) return true;

                board[row][col] = 0;
              }
            }

            return false;
          }
        }
      }

      return true;
    }

    solve(board);

    return board;
  }
}

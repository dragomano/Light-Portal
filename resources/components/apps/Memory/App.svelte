<script lang="ts">
  import MemoryCard from "./MemoryCard.svelte";
  import { symbols } from "./symbols";
  import { _ } from 'svelte-i18n';

  let cards = $state([]);
  let moves = $state(0);
  let matchedCards = $state.raw(new Set<number>());
  let openedCards = $state.raw(new Set<number>());

  let totalPairs = symbols.length * 2;
  let matches = $derived(matchedCards.size);
  let isGameWon = $derived(matches === totalPairs);

  function shuffleCards<T>(cards: T[]): T[] {
    return [...cards].sort(() => Math.random() - 0.5);
  }

  function resetGame() {
    moves = 0;
    matchedCards = new Set();
    openedCards = new Set();

    setTimeout(() => {
      cards = shuffleCards([...symbols, ...symbols]);
    }, 500);
  }

  function openCard(index: number) {
    if (openedCards.has(index) || openedCards.size >= 2) return;

    openedCards = new Set([...openedCards, index]);
    moves += 1;

    if (openedCards.size === 2) {
      setTimeout(() => checkMatch(), 1000);
    }
  }

  function checkMatch() {
    const [firstIndex, secondIndex] = [...openedCards];

    if (cards[firstIndex].name === cards[secondIndex].name) {
      matchedCards = new Set([...matchedCards, firstIndex, secondIndex]);
    }

    openedCards = new Set();
  }

  function getStatus(index: number): "opened" | "matched" | "closed" {
    if (matchedCards.has(index)) return "matched";

    if (openedCards.has(index)) return "opened";

    return "closed";
  }

  resetGame();
</script>

<div class="memory_container">
  <div class="game-info">
    <div>{$_('moves')}{moves}</div>
    <div>{$_('matches')}{matches} / {totalPairs}</div>
  </div>

  <div class="board">
    {#each cards as card, index}
      <MemoryCard
        image={card.image}
        status={getStatus(index)}
        disabled={openedCards.size === 2}
        onclick={() => openCard(index)}
      />
    {/each}
  </div>

  <button onclick={resetGame}>{$_('new_game')}</button>

  {#if isGameWon}
    <div class="win-message">
      {$_('congratulations', { values: { moves } })}
    </div>
  {/if}
</div>

<style lang="scss">
  .memory_container {
    width: 100%;
    max-width: 800px;
    text-align: center;

    .game-info {
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      padding: 0 10px;

      div {
        font-size: 1.2rem;
        font-weight: bold;
        color: #34495e;
      }
    }

    .board {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      grid-gap: 15px;
      margin: 0 auto;
    }

    button {
      margin-top: 20px;
      padding: 5px 10px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
      height: auto !important;

      &:hover {
        background-color: #2980b9;
      }
    }

    .win-message {
      margin-top: 20px;
      font-size: 1.5rem;
      color: #27ae60;
      font-weight: bold;
    }

    @media (max-width: 600px) {
      .board {
        grid-template-columns: repeat(3, 1fr);
      }
      :global(.card) {
        height: 100px;
      }
    }
  }
</style>

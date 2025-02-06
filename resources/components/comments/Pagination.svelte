<script>
  import { _ } from 'svelte-i18n';
  import Button from '../BaseButton.svelte';

  let { start = $bindable(0), totalItems, itemsPerPage, totalVisible = 5 } = $props();

  const showPagination = $derived(Math.ceil(totalItems / itemsPerPage) > 1);
  const currentPage = $derived(Math.floor(start / itemsPerPage) + 1);
  const totalPages = $derived(Math.ceil(totalItems / itemsPerPage));

  const visiblePages = $derived.by(() => {
    const pages = [];
    const halfVisiblePages = Math.floor(totalVisible / 2);
    const firstVisiblePage = Math.max(1, currentPage - halfVisiblePages);
    const lastVisiblePage = Math.min(totalPages, currentPage + halfVisiblePages);

    if (firstVisiblePage > 1) {
      pages.push({ number: 1, text: '1' });

      if (firstVisiblePage > 2) {
        pages.push({ number: firstVisiblePage - 1, text: '...' });
      }
    }

    for (let i = firstVisiblePage; i <= lastVisiblePage; i++) {
      pages.push({ number: i, text: i.toString() });
    }

    if (lastVisiblePage < totalPages) {
      if (lastVisiblePage < totalPages - 1) {
        pages.push({ number: lastVisiblePage + 1, text: '...' });
      }

      pages.push({
        number: totalPages,
        text: totalPages.toString()
      });
    }

    return pages;
  });

  const changePage = (page) => {
    start = page * itemsPerPage - itemsPerPage;
  };
</script>

{#if showPagination}
  <div class="centertext">
    <div class="pagesection">
      <Button
        icon="arrow_left"
        disabled={currentPage === 1}
        aria-label={$_('prev')}
        onclick={() => changePage(currentPage - 1)}
      />

      {#each visiblePages as page}
        <Button
          disabled={page.text === '...'}
          class={['nav-page', page.number === currentPage && 'active']}
          onclick={() => changePage(page.number)}
        >
          {page.text}
        </Button>
      {/each}

      <Button
        icon="arrow_right"
        disabled={currentPage === totalPages}
        aria-label={$_('next')}
        onclick={() => changePage(currentPage + 1)}
      />
    </div>
  </div>
{/if}

<template>
  <div v-if="showPagination" class="col-xs-12 centertext">
    <div class="pagesection">
      <Button
        icon="arrow_left"
        :disabled="currentPage === 1"
        :aria-label="$t('prev')"
        @click="changePage(currentPage - 1)"
      />

      <Button
        v-for="page in visiblePages"
        :key="page.number"
        :disabled="page.text === '...'"
        :class="['nav-page', page.number === currentPage && 'active']"
        @click="changePage(page.number)"
      >
        {{ page.text }}
      </Button>

      <Button
        icon="arrow_right"
        :disabled="currentPage === totalPages"
        :aria-label="$t('next')"
        @click="changePage(currentPage + 1)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'PurePagination',
};
</script>

<script setup>
import { computed } from 'vue';
import Button from './BaseButton.vue';

const props = defineProps({
  start: {
    type: Number,
    default: 1,
  },
  totalItems: {
    type: Number,
    required: true,
  },
  itemsPerPage: {
    type: Number,
    required: true,
  },
  totalVisible: {
    type: Number,
    default: 5,
  },
});

const emit = defineEmits(['update:start']);

const showPagination = computed(() => totalPages.value > 1);
const currentPage = computed(() => Math.floor(props.start / props.itemsPerPage) + 1);
const totalPages = computed(() => Math.ceil(props.totalItems / props.itemsPerPage));

const visiblePages = computed(() => {
  const pages = [];
  const halfVisiblePages = Math.floor(props.totalVisible / 2);
  const firstVisiblePage = Math.max(1, currentPage.value - halfVisiblePages);
  const lastVisiblePage = Math.min(totalPages.value, currentPage.value + halfVisiblePages);

  if (firstVisiblePage > 1) {
    pages.push({ number: 1, text: '1' });

    if (firstVisiblePage > 2) {
      pages.push({ number: firstVisiblePage - 1, text: '...' });
    }
  }

  for (let i = firstVisiblePage; i <= lastVisiblePage; i++) {
    pages.push({ number: i, text: i.toString() });
  }

  if (lastVisiblePage < totalPages.value) {
    if (lastVisiblePage < totalPages.value - 1) {
      pages.push({ number: lastVisiblePage + 1, text: '...' });
    }

    pages.push({ number: totalPages.value, text: totalPages.value.toString() });
  }

  return pages;
});

const changePage = (page) => emit('update:start', page * props.itemsPerPage - props.itemsPerPage);
</script>

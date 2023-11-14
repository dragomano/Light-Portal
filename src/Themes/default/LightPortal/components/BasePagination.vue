<template>
  <div v-if="showPagination" class="col-xs-12 centertext">
    <div class="pagesection">
      <Button
        icon="arrow_left"
        :disabled="start === 0"
        :aria-label="$t('prev')"
        @click="changeStart(start - limit)"
      />

      <Button
        v-for="page in totalPages"
        class="nav-page"
        :class="{ active: page === activePage }"
        :key="page"
        @click="changeStart(page * limit - limit)"
      >
        {{ page }}
      </Button>

      <Button
        icon="arrow_right"
        :disabled="start === maxStart"
        :aria-label="$t('next')"
        @click="changeStart(start + limit)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
};
</script>

<script setup>
import { computed, onMounted } from 'vue';
import Button from './BaseButton.vue';

const props = defineProps({
  total: {
    type: Number,
    required: true,
  },
  limit: {
    type: Number,
    required: true,
  },
  start: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['change-start']);

const showPagination = computed(() => totalPages.value > 1);
const activePage = computed(() => Math.floor(props.start / props.limit) + 1);
const totalPages = computed(() => Math.ceil(props.total / props.limit));
const maxStart = computed(() => totalPages.value * props.limit - props.limit);

const changeStart = (start) => emit('change-start', start);

onMounted(() => {
  if (props.start > maxStart.value) changeStart(0);
});
</script>

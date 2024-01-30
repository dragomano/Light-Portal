<template>
  <div v-if="userStore.id" class="reply_form roundframe descbox">
    <MarkdownEditor v-model="message" :placeholder="$t('add_comment')" />

    <slot></slot>

    <Button icon="submit" name="comment" :disabled="!message" @click="submit">
      {{ $t('post') }}
    </Button>
  </div>
</template>

<script>
export default {
  name: 'ReplyForm',
};
</script>

<script setup>
import { ref } from 'vue';
import { useUserStore } from '../../scripts/light_portal/dev/comment_stores.js';
import MarkdownEditor from './MarkdownEditor.vue';
import Button from './BaseButton.vue';

const props = defineProps({
  parent: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['submit']);

const userStore = useUserStore();

const message = ref('');

const submit = () => {
  emit('submit', { parent: props.parent, content: message.value });

  message.value = '';
};
</script>

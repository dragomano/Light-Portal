<template>
  <div v-if="userStore.id" class="reply_form roundframe descbox">
    <MarkdownPreview v-show="preview && message" :content="message" />

    <textarea
      ref="textarea"
      v-model="message"
      v-focus
      aria-labelledby="bottom-label"
      :placeholder="$t('lp_comment_placeholder')"
      @focus="focus"
    ></textarea>

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
import MarkdownPreview from './MarkdownPreview.vue';
import Button from './BaseButton.vue';

const props = defineProps({
  parent: {
    type: Object,
    default: null,
  },
  preview: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['submit']);
const userStore = useUserStore();
const message = ref('');
const textarea = ref();

const focus = () => (textarea.value.style.height = '100px');

const submit = () => {
  emit('submit', { parent: props.parent, content: message.value });

  textarea.value.style.height = 'auto';
  message.value = '';
};
</script>

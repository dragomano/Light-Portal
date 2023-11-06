<template>
  <div @keyup.esc="handleCancel">
    <MarkdownPreview v-show="content" :content="content" />

    <textarea v-model="content" v-focus required></textarea>

    <div class="comment_buttons">
      <span v-show="content" @click="handleSubmit" v-html="iconStore.save + $t('save')"></span>
      <span @click="handleCancel" v-html="iconStore.undo + $t('modify_cancel')"></span>
      <span></span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditForm',
};
</script>

<script setup>
import { ref } from 'vue';
import { useIconStore } from 'stores';
import MarkdownPreview from './MarkdownPreview.vue';

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['submit', 'cancel']);
const iconStore = useIconStore();
const content = ref(props.comment.message);

const handleSubmit = () => emit('submit', { id: props.comment.id, content: content.value });

const handleCancel = () => emit('cancel');
</script>

<style scoped>
textarea {
  resize: none;
  width: 100%;
  height: 100px;
}
</style>

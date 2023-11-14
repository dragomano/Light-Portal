<template>
  <div class="edit_form" @keyup.esc="handleCancel">
    <MarkdownPreview v-show="content" :content="content" />

    <textarea v-model="content" v-focus required></textarea>

    <div class="comment_edit_buttons">
      <Button v-show="content" @click="handleSubmit" view="span" icon="save">
        {{ $t('save') }}
      </Button>
      <Button @click="handleCancel" view="span" icon="undo">
        {{ $t('modify_cancel') }}
      </Button>
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
import { useIconStore } from '../../scripts/light_portal/dev/base_stores.js';
import MarkdownPreview from './MarkdownPreview.vue';
import Button from './BaseButton.vue';

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

<template>
  <div class="edit_form" @keyup.esc="handleCancel">
    <MarkdownEditor v-model="message" required />

    <div class="comment_edit_buttons">
      <Button v-show="message" @click="handleSubmit" view="span" icon="save">
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
import MarkdownEditor from './MarkdownEditor.vue';
import Button from './BaseButton.vue';

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['submit', 'cancel']);

const iconStore = useIconStore();

const message = ref(props.comment.message);

const handleSubmit = () => emit('submit', { id: props.comment.id, content: message.value });

const handleCancel = () => emit('cancel');
</script>

<template>
  <MarkdownPreview v-show="message" class="bg odd" :content="message" />

  <markdown-toolbar :for="uuid">
    <md-bold><Button icon="bold" :aria-label="$t('bold')" /></md-bold>
    <md-italic><Button icon="italic" :aria-label="$t('italic')" /></md-italic>
    <md-quote><Button icon="quote" :aria-label="$t('quote')" /></md-quote>
    <md-code><Button icon="code" :aria-label="$t('code')" /></md-code>
    <md-link><Button icon="link" :aria-label="$t('link')" /></md-link>
    <md-image><Button icon="image" :aria-label="$t('image')" /></md-image>
    <md-unordered-list><Button icon="list" :aria-label="$t('list')" /></md-unordered-list>
    <md-task-list><Button icon="task" :aria-label="$t('task_list')" /></md-task-list>
  </markdown-toolbar>

  <textarea ref="textarea" v-model="message" v-bind="$attrs" :id="uuid"></textarea>
</template>

<script setup>
import { defineEmits, getCurrentInstance, ref, computed, onMounted } from 'vue';
import MarkdownPreview from './MarkdownPreview.vue';
import Button from './BaseButton.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const instance = getCurrentInstance();
const uuid = ref(instance.uid);
const textarea = ref();

const message = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

onMounted(() => textarea.value.focus());
</script>

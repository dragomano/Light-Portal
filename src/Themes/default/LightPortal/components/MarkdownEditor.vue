<template>
  <MarkdownPreview v-show="message" class="bg odd" :content="message" />

  <markdown-toolbar :for="uuid">
    <md-bold><Button icon="bold" /></md-bold>
    <md-italic><Button icon="italic" /></md-italic>
    <md-quote><Button icon="quote" /></md-quote>
    <md-code><Button icon="code" /></md-code>
    <md-link><Button icon="link" /></md-link>
    <md-image><Button icon="image" /></md-image>
    <md-unordered-list><Button icon="list" /></md-unordered-list>
    <md-task-list><Button icon="task" /></md-task-list>
  </markdown-toolbar>

  <textarea ref="textarea" v-model="message" v-bind="$attrs" :id="uuid"></textarea>
</template>

<script setup>
import { getCurrentInstance, ref, computed, onMounted } from 'vue';
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

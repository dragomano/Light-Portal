<template>
  <br class="clear" />
  <transition name="fade">
    <div v-show="show" class="roundframe" :id="blockId">
      <div class="title_bar">
        <h5 class="titlebg">{{ $t('settings') }}</h5>
      </div>
      <div class="noticebox">
        <form ref="form" class="form_settings" :id="formId" @submit.prevent="saveSettings">
          <input type="hidden" name="plugin_name" :value="item.snake_name" />
          <input type="hidden" :name="sessionVar" :value="sessionId" />

          <PluginOptionItem
            v-for="(option, index) in item.settings"
            :option="option"
            :plugin="String(item.snake_name)"
            :key="index"
          />
        </form>
      </div>
      <div class="footer">
        <span class="infobox floatleft" :class="success ? 'show' : undefined">
          {{ $t('settings_saved') }}
        </span>
        <Button icon="close" @click="emit('hide')">{{ $t('find_close') }}</Button>
        <Button v-if="item.saveable" icon="save" :form="formId" type="submit">
          {{ $t('save') }}
        </Button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { defineEmits, ref, computed } from 'vue';
import { useContextStore } from '@scripts/base_stores.js';
import { useAppStore } from '@scripts/plugin_stores.js';
import Button from './BaseButton.vue';
import PluginOptionItem from './PluginOptionItem.vue';

const { sessionVar, sessionId } = useAppStore();
const { postUrl } = useContextStore();

const props = defineProps({
  item: {
    type: {
      snake_name: String,
      settings: Array,
      saveable: Boolean,
    },
    required: true,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['hide']);

const success = ref(false);
const form = ref();

const blockId = computed(() => props.item.snake_name + '_' + sessionId + '_settings');
const formId = computed(() => props.item.snake_name + '_form_' + sessionId);

const saveSettings = async () => {
  let formData = new FormData(form.value),
    lpCheckboxes = form.value.querySelectorAll('input[type=checkbox]');

  lpCheckboxes.forEach(function (val) {
    formData.append(val.getAttribute('name'), val.matches(':checked'));
  });

  const { data } = await axios.post(postUrl, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });

  if (data.success) {
    success.value = true;

    setInterval(() => (success.value = false), 3000);
  }
};
</script>

<template>
  <Multiselect
    mode="single"
    :id="id"
    :name="name"
    :options="options"
    :value="value"
    :append-to-body="true"
    :can-clear="false"
    :native-support="true"
    :placeholder="$t('lp_plugins_select')"
    :no-results-text="$t('no_matches')"
  />
</template>

<script>
export default {
  name: 'OptionSelect',
};
</script>

<script setup>
import { computed } from 'vue';
import Multiselect from '@vueform/multiselect';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  option: {
    type: Object,
    required: true,
  },
});

const extra = computed(() => props.option[2]);

const options = computed(() =>
  extra.value ? Object.entries(extra.value).map(([value, label]) => ({ label, value })) : null
);
</script>

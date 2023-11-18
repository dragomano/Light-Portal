<template>
  <input type="hidden" :name="`${name}[]`" value="" />
  <Multiselect
    mode="tags"
    :id="id"
    :name="name"
    :options="options"
    :value="multiSelect"
    :append-to-body="true"
    :close-on-select="false"
    :searchable="true"
    :native-support="true"
    :placeholder="$t('lp_plugins_select')"
    :no-results-text="$t('no_matches')"
  />
</template>

<script>
export default {
  name: 'OptionMultiSelect',
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
const multiSelect = computed(() => props.value?.split(','));

const options = computed(() =>
  extra.value ? Object.entries(extra.value).map(([value, label]) => ({ label, value })) : null
);
</script>

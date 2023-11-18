<template>
  <div class="checkbox_field">
    <Toggle :labelledby="id" :name="name" v-model="toggler" true-value="1" false-value="0">
      <template v-slot:label="{ classList }">
        <label :id="id" :class="classList.label">{{ $t(`lp_${id}`) }}</label>
      </template>
    </Toggle>
  </div>
</template>

<script>
export default {
  name: 'OptionCheck',
};
</script>

<script setup>
import { ref } from 'vue';
import Toggle from '@vueform/toggle';

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
});

const toggler = ref(!!props.value);
</script>

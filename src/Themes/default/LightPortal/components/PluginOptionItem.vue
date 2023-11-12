<template>
  <div>
    <template v-if="showLabel">
      <label :for="id">
        {{ $t(txtKey) }}
        <span v-if="postfix" class="postfix">({{ postfix }})</span>
      </label>
    </template>
    <template v-if="isType('text')">
      <div>
        <input
          type="text"
          :id="id"
          :name="name"
          :value="value"
          :pattern="pattern"
          :required="required"
        />
      </div>
    </template>
    <template v-if="isType('large_text')">
      <div>
        <textarea :id="id" :name="name">{{ value }}</textarea>
      </div>
    </template>
    <template v-if="isType('url')">
      <div>
        <input type="url" :id="id" :name="name" :value="value" :placeholder="placeholder" />
      </div>
    </template>
    <template v-if="isType('color')">
      <div>
        <input :id="id" :name="name" :value="value" ref="colorInput" data-jscolor="{}" v-once />
      </div>
    </template>
    <template v-if="isType('number')">
      <div>
        <input
          type="number"
          :id="id"
          :name="name"
          :value="value"
          :min="min"
          :max="max"
          :step="step"
        />
      </div>
    </template>
    <template v-if="isType('check')">
      <div class="checkbox_field">
        <Toggle :name="name" v-model="toggler" true-value="1" false-value="0" :labelledby="id" />
        <label :id="id">{{ $t(txtKey) }}</label>
      </div>
    </template>
    <template v-if="isType('multicheck')">
      <fieldset class="bbc_code">
        <ul>
          <li v-for="(option_label, key) in extra" :key="key">
            <label :for="`${name}[${key}]`">
              <input
                type="checkbox"
                :id="`${name}[${key}]`"
                :name="`${name}[${key}]`"
                :checked="multicheckOptions[key]"
                value="1"
              />{{ option_label }}</label
            >
          </li>
        </ul>
      </fieldset>
    </template>
    <template v-if="isType('select')">
      <input v-if="multiple" type="hidden" :name="name + '[]'" value="" />
      <Multiselect
        v-if="multiple"
        v-model="multiSelect"
        mode="tags"
        :allow-absent="true"
        :append-to-body="true"
        :id="id"
        :name="name"
        :options="options"
        :close-on-select="false"
        :searchable="true"
        :native-support="true"
        :placeholder="$t('lp_plugins_select')"
        :no-results-text="$t('no_matches')"
        @clear="clear"
      />
      <Multiselect
        v-else
        v-model="multiSelect"
        mode="single"
        :append-to-body="true"
        :can-clear="false"
        :id="id"
        :name="name"
        :options="options"
        :searchable="true"
        :native-support="true"
        :placeholder="$t('lp_plugins_select')"
        :no-results-text="$t('no_matches')"
      />
    </template>
    <template v-if="isType('title')">
      <div class="sub_bar">
        <h6 class="subbg">{{ $t(txtKey) }}</h6>
      </div>
    </template>
    <template v-if="isType('desc')">
      <div class="roundframe">{{ $t(txtKey) }}</div>
    </template>
    <template v-if="subtext">
      <div class="roundframe" v-html="subtext"></div>
    </template>
    <template v-if="isType('callback')">
      <span v-html="extra"></span>
    </template>
  </div>
</template>

<script>
export default {
  name: 'PluginOptionItem',
};
</script>

<script setup>
import { toRefs, ref, computed, onMounted } from 'vue';
import { useContextStore } from 'stores';
import Button from './BaseButton.vue';
import Multiselect from '@vueform/multiselect';
import Toggle from '@vueform/toggle';

const contextStore = useContextStore();

const props = defineProps({
  option: {
    type: Object,
    required: true,
  },
  plugin: {
    type: String,
    required: true,
  },
});

const { option, plugin } = toRefs(props);

const type = computed(() => option.value[0]);
const name = computed(() => option.value[1]);
const extra = computed(() => option.value[2]);
const required = computed(() => option.value.required);
const pattern = computed(() => option.value.pattern);
const postfix = computed(() => option.value.postfix);
const subtext = computed(() => option.value.subtext);
const placeholder = computed(() => option.value.placeholder);
const min = computed(() => option.value.min ?? 0);
const max = computed(() => option.value.max);
const step = computed(() => option.value.step ?? (type.value === 'int' ? 1 : 0.01));
const multiple = computed(() => option.value.multiple);

const showLabel = computed(() => !['callback', 'title', 'desc', 'check'].includes(type.value));
const txtKey = computed(() => `lp_${plugin.value}.${name.value}`);
const id = computed(() => `${plugin.value}_${name.value}`);
const value = computed(() => contextStore[`lp_${plugin.value}`]?.[name.value]);

const isType = (t) =>
  (t === 'number' && ['float', 'int'].includes(type.value)) ||
  (t === type.value && (extra.value || true));

const isJSON = (str) => {
  try {
    JSON.parse(str);
    return true;
  } catch (error) {
    return false;
  }
};

const multicheckOptions = computed(
  () => isType('multicheck') && isJSON(value.value) && JSON.parse(value.value)
);

const options = computed(() =>
  extra.value ? Object.entries(extra.value).map(([value, label]) => ({ label, value })) : null
);

const multiSelect = computed(() =>
  isType('select') && multiple.value ? value.value?.split(',') : value.value
);

const toggler = ref(!!value.value);

const colorInput = ref(null);

onMounted(() => jscolor.install(colorInput.value));
</script>

<style scoped>
.postfix {
  margin-left: 4px;
}
</style>

<style>
.multiselect-tags-search {
  box-shadow: none;
  height: auto !important;
  line-height: unset;
}
</style>

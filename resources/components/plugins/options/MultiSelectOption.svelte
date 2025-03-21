<script lang="ts">
  import { SelectOption } from './index.js';

  interface Props {
    id?: string;
    name?: string;
    value?: any;
    option?: object;
  }

  let { id, name, value = $bindable(''), option = {} }: Props = $props();
  let values = $state(value.split(',').filter(Boolean))
</script>

<input type="hidden" {name} value={values} />

<SelectOption
  {id}
  {option}
  bind:value={values}
  multiple={true}
  clearable={true}
/>

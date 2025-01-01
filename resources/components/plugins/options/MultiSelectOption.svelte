<script>
  import { SelectOption } from './index.js';

  let { id, name, value = '', option = {} } = $props();
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

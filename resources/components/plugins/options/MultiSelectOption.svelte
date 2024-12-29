<script>
  import { SelectOption } from './index.js';

  let { id, name, value = $bindable(''), option = {} } = $props();

  value = value?.split(',') || []
</script>

<SelectOption
  {id}
  name={`${name}[]`}
  {option}
  bind:value
  multiple={true}
  clearable={true}
/>

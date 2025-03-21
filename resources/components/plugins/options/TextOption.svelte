<script>
  let { id, name, value = '', option = {} } = $props();

  let pattern = $state(option.pattern);
  let placeholder = $state(option.placeholder);
  let required = $state(option.required);
</script>

<div>
  <input type="text" {id} {name} bind:value {pattern} {placeholder} {required} />
</div>

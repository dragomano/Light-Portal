<script>
  let { id, name, value = '', option = {} } = $props();

  let placeholder = $state(option.placeholder);
  let required = $state(option.required);
</script>

<div>
  <input type="url" {id} {name} bind:value {placeholder} {required} />
</div>

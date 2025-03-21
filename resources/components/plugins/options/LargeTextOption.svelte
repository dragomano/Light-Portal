<script lang="ts">
  let { id, name, value = '' } = $props();
</script>

<div>
  <textarea {id} {name} bind:value></textarea>
</div>

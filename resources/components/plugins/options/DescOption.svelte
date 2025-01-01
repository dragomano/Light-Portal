<script>
  import { _ } from 'svelte-i18n';

  let { id } = $props();
</script>

<div class="roundframe">{@html $_(`lp_${id}`)}</div>

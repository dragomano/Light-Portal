<script>
  import { _ } from 'svelte-i18n';

  let { id } = $props();
</script>

<div class="sub_bar">
  <h6 class="subbg">{$_(`lp_${id}`)}</h6>
</div>

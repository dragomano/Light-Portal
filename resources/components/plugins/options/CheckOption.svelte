<script>
  import { _ } from 'svelte-i18n';
  import Toggle from 'svelte-toggle';

  let { id, name, value = '' } = $props();
  let toggled = $state(!!value);
</script>

<div class="checkbox_field">
  <Toggle bind:toggled small label={$_(`lp_${id}`)} />
  <input type="hidden" {name} bind:value={toggled} />
</div>

<style>
  :global {
    .checkbox_field {
      padding-top: 0.8em;
      display: flex;
      flex-direction: row;
      gap: 1em;
      justify-content: space-between;
    }

    .checkbox_field label {
      font-size: 0.8rem;
    }
  }
</style>

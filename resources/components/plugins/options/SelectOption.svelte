<script>
  import { _ } from 'svelte-i18n';
  import Svelecte, { config } from 'svelecte';

  config.i18n.empty = $_('no_options');
  config.i18n.nomatch = $_('no_matches');

  let {
    id,
    name,
    value = $bindable(''),
    option = {},
    multiple = false,
    clearable = false
  } = $props();

  const params = $derived(option[2]);
  const options = $derived(
    params ? Object.entries(params).map(([value, label]) => ({ label, value })) : []
  );
</script>

<Svelecte
  {options}
  inputId={id}
  {name}
  bind:value
  {multiple}
  {clearable}
  placeholder={$_('lp_plugins_select')}
/>

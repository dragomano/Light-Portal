<script lang="ts">
  import { _ } from 'svelte-i18n';
  import Svelecte, { config } from 'svelecte';

  config.i18n.empty = $_('no_options');
  config.i18n.nomatch = $_('no_matches');

  interface Props {
    id?: string;
    name?: string;
    value?: any;
    option?: object;
    multiple?: boolean;
    clearable?: boolean;
  }

  let {
    id,
    name,
    value = $bindable(''),
    option = {},
    multiple = false,
    clearable = false
  }: Props = $props();

  const params = $derived(option[2]);
  const options = $derived(
    params ? Object.entries(params).map(([value, label]) => ({ value, label })) : []
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

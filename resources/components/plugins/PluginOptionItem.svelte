<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { contextState } from '../../js/states.svelte';
  import {
    CallbackOption,
    CheckOption,
    ColorOption,
    DescOption,
    LargeTextOption,
    MultiSelectOption,
    NumberOption,
    Postfix,
    RangeOption,
    SelectOption,
    TextOption,
    TitleOption,
    UrlOption
  } from './options/index.js';
  import type { Component } from 'svelte';

  let { option, plugin } = $props();

  let type = $derived(option[0]);
  let name = $derived(option[1]);
  let id = $derived(`${plugin}.${name}`);
  let value = $derived(contextState[`lp_${plugin}`]?.[name]);
  let { postfix, subtext } = $derived(option);

  const showLabel = $derived(!['callback', 'title', 'desc', 'check'].includes(type));
  const optionType = $derived(['float', 'int'].includes(type) ? 'number' : type);

  const dynamicProps = $derived.by(() => {
    switch (optionType) {
      case 'callback':
        return { Component: CallbackOption, option };
      case 'check':
        return { Component: CheckOption, id, name, value };
      case 'color':
        return { Component: ColorOption, id, name, value };
      case 'desc':
        return { Component: DescOption, id };
      case 'large_text':
        return { Component: LargeTextOption, id, name, value };
      case 'multiselect':
        return { Component: MultiSelectOption, id, name, value, option };
      case 'number':
        return { Component: NumberOption, id, name, value, option };
      case 'range':
        return { Component: RangeOption, id, name, value, option };
      case 'select':
        return { Component: SelectOption, id, name, value, option };
      case 'text':
        return { Component: TextOption, id, name, value, option };
      case 'title':
        return { Component: TitleOption, id };
      case 'url':
        return { Component: UrlOption, id, name, value, option };
      default:
        return null;
    }
  });

  const DynamicComponent = $derived<Component>(dynamicProps?.Component);
</script>

<div>
  {#if showLabel}
    <label for={id}>
      {$_(`lp_${id}`)}
      {#if postfix && optionType !== 'number'}
        <Postfix>({postfix})</Postfix>
      {/if}
    </label>
  {/if}

  {#if dynamicProps}
    <DynamicComponent {...dynamicProps} />
  {/if}

  {#if subtext}
    <div class="roundframe">{@html subtext}</div>
  {/if}
</div>

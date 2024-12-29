<script>
  import { _ } from 'svelte-i18n';
  import { get, derived } from 'svelte/store';
  import { useContextStore } from '../../js/stores.js';
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

  let { option, plugin } = $props();

  const contextStore = get(useContextStore);
  const type = $derived(option[0]);
  const name = $derived(option[1]);
  const id = $derived(`${plugin}.${name}`);
  const value = $derived(contextStore[`lp_${plugin}`]?.[name]);
  const { postfix, subtext } = $derived(option);
  const showLabel = $derived(!['callback', 'title', 'desc', 'check'].includes(type));
  const optionType = $derived(['float', 'int'].includes(type) ? 'number' : type);

  const dynamicProps = $derived.by(() => {
    let componentProps;

    switch (optionType) {
      case 'callback':
        componentProps = { Component: CallbackOption, option };
        break;
      case 'check':
        componentProps = { Component: CheckOption, id, name, value };
        break;
      case 'color':
        componentProps = { Component: ColorOption, id, name, value };
        break;
      case 'desc':
        componentProps = { Component: DescOption, id };
        break;
      case 'large_text':
        componentProps = { Component: LargeTextOption, id, name, value };
        break;
      case 'multiselect':
        componentProps = { Component: MultiSelectOption, id, name, value, option };
        break;
      case 'number':
        componentProps = { Component: NumberOption, id, name, value, option };
        break;
      case 'range':
        componentProps = { Component: RangeOption, id, name, value, option };
        break;
      case 'select':
        componentProps = { Component: SelectOption, id, name, value, option };
        break;
      case 'text':
        componentProps = { Component: TextOption, id, name, value, option };
        break;
      case 'title':
        componentProps = { Component: TitleOption, id };
        break;
      case 'url':
        componentProps = { Component: UrlOption, id, name, value, option };
        break;
      default:
        componentProps = null;
    }

    return componentProps;
  });

  const Component = $derived(dynamicProps.Component);
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
    <Component {...dynamicProps} />
  {/if}

  {#if subtext}
    <div class="roundframe">{@html subtext}</div>
  {/if}
</div>

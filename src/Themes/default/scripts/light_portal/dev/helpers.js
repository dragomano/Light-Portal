class VueAdapter {
  mount(component, selector, modules = {}) {
    const options = {
      moduleCache: {
        vue: Vue,
        '@vueuse/core': window.VueUse,
        ...modules,
      },

      async getFile(url) {
        if (url.includes('.vue')) url = smf_default_theme_url + '/LightPortal/components/' + url;

        const res = await fetch(url);

        if (!res.ok) throw Object.assign(new Error(res.statusText + ' ' + url), { res });

        return {
          getContentData: (asBinary) => (asBinary ? res.arrayBuffer() : res.text()),
        };
      },

      addStyle(textContent) {
        const style = Object.assign(document.createElement('style'), { textContent });
        const ref = document.head.getElementsByTagName('style')[0] || null;

        document.head.insertBefore(style, ref);
      },
    };

    const { loadModule } = window['vue3-sfc-loader'];

    const app = Vue.createApp({
      components: {
        [component]: Vue.defineAsyncComponent(() => loadModule(`${component}.vue`, options)),
      },

      template: `<${component}></${component}>`,
    });

    const { createPinia } = window.Pinia;

    app.use(createPinia());

    const rules = import('./plurals.js').then((m) => new m.default().rules());

    const i18n = VueI18n.createI18n({
      locale: vueGlobals.context.locale,
      pluralizationRules: rules,
      messages: {
        [vueGlobals.context.locale]: vueGlobals.txt,
      },
    });

    app.use(i18n);

    if (window.VueShowdownPlugin) {
      const classMap = {
        blockquote: 'bbc_standard_quote',
        code: 'bbc_code',
        h1: 'titlebg',
        h2: 'titlebg',
        h3: 'titlebg',
        image: 'bbc_img',
        a: 'bbc_link',
        ul: 'bbc_list',
        table: 'table_grid',
        tr: 'windowbg',
      };

      const bindings = Object.keys(classMap).map((key) => ({
        type: 'output',
        regex: new RegExp(`<${key}(.*)>`, 'g'),
        replace: `<${key} class="${classMap[key]}" $1>`,
      }));

      window.showdown.extension('bindings', bindings);

      app.use(VueShowdownPlugin, {
        flavor: 'github',
        options: {
          emoji: true,
          encodeEmails: true,
          openLinksInNewWindow: true,
        },
      });
    }

    app.mount(selector);
  }
}

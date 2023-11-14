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

    const plurals = new Pluralization();

    const i18n = VueI18n.createI18n({
      locale: vueGlobals.context.locale,
      pluralizationRules: plurals.rules(),
      messages: {
        [vueGlobals.context.locale]: vueGlobals.txt,
      },
    });

    app.use(i18n);

    if (window.VueShowdownPlugin)
      app.use(VueShowdownPlugin, {
        flavor: 'github',
        options: {
          emoji: true,
        },
      });

    app.directive('focus', {
      mounted(el) {
        el.focus();
      },
    });

    app.mount(selector);
  }
}

class Pluralization {
  slavianRule(choice, choicesLength) {
    if (choice === 0) {
      return 0;
    }

    const teen = choice > 10 && choice < 20;
    const endsWithOne = choice % 10 === 1;

    if (!teen && endsWithOne) {
      return 1;
    }

    if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
      return 2;
    }

    return choicesLength < 4 ? 2 : 3;
  }

  rules() {
    return {
      ru: this.slavianRule,
      uk: this.slavianRule,
    };
  }
}

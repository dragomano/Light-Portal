import { defineConfig } from 'vitepress'
import { shared } from './shared'
import en from './en'
import ru from './ru'
import el from './el'
import it from './it'
import ar from './ar'
import es from './es'
import de from './de'
import nl from './nl'
import pl from './pl'
import uk from './uk'
import fr from './fr'
import tr from './tr'
import sl from './sl'

function addPrefixToLinks(obj) {
  const prefix = obj.lang

  function processNavItems(items) {
    return items.map(item => {
      if (item.link) {
        item.link = `/${prefix}${item.link}`.replace(/\/+/g, '/');
      }

      if (item.items) {
        item.items = processNavItems(item.items);
      }

      return item;
    });
  }

  if (obj.themeConfig?.nav) {
    obj.themeConfig.nav = processNavItems(obj.themeConfig.nav);
  }

  return obj;
}

// https://vitepress.dev/reference/site-config
export default defineConfig({
  ...shared,
  vite: {
    builder: 'rolldown'
  },
  locales: {
    root: { label: 'English', ...en },
    ...Object.fromEntries(
      [
        { code: 'ru', label: 'Русский', config: ru },
        { code: 'el', label: 'Αγγλικά', config: el },
        { code: 'it', label: 'Italiano', config: it },
        { code: 'ar', label: 'العربية', config: ar, dir: 'rtl' },
        { code: 'es', label: 'Español', config: es },
        { code: 'de', label: 'Deutsch', config: de },
        { code: 'nl', label: 'Dutch', config: nl },
        { code: 'pl', label: 'Polski', config: pl },
        { code: 'uk', label: 'Українська', config: uk },
        { code: 'fr', label: 'Français', config: fr },
        { code: 'tr', label: 'Türkçe', config: tr },
        { code: 'sl', label: 'Slovenščina', config: sl }
      ].map(({ code, label, config, dir }) => [
        code,
        {
          label,
          ...addPrefixToLinks(config),
          ...(dir && { dir })
        }
      ])
    )
  },
})

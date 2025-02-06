import en from './en';
import ru from './ru';
import el from './el';
import it from './it';
import ar from './ar';
import es from './es';
import de from './de';
import nl from './nl';
import pl from './pl';
import uk from './uk';
import fr from './fr';
import tr from './tr';
import sl from './sl';

function addPrefixToLinks(prefix, obj) {
  for (const key in obj) {
    if (typeof obj[key] === 'object' && obj[key] !== null) {
      addPrefixToLinks(prefix, obj[key]);
    } else if (key === 'link' && !obj[key].startsWith('https')) {
      obj[key] = `/${prefix}` + obj[key];
    }
  }

  return obj;
}

export default {
  root: en,
  ru: addPrefixToLinks('ru', ru),
  el: addPrefixToLinks('el', el),
  it: addPrefixToLinks('it', it),
  ar: addPrefixToLinks('ar', ar),
  es: addPrefixToLinks('es', es),
  de: addPrefixToLinks('de', de),
  nl: addPrefixToLinks('nl', nl),
  pl: addPrefixToLinks('pl', pl),
  uk: addPrefixToLinks('uk', uk),
  fr: addPrefixToLinks('fr', fr),
  tr: addPrefixToLinks('tr', tr),
  sl: addPrefixToLinks('sl', sl),
};

import en from './en';
import ru from './ru';
import el from './el';
import it from './it';

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
};

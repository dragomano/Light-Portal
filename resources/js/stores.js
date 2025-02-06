import { writable } from 'svelte/store';

export const localStore = (key, initialValue) => {
  const storedValue = localStorage.getItem(key);
  const store = writable(storedValue !== null ? storedValue : initialValue);

  store.subscribe((value) => {
    localStorage.setItem(key, value.toString());
  });

  return store;
}

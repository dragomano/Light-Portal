import { writable } from 'svelte/store'

/**
 * @template T
 * @param {string} key
 * @param {T} initialValue
 * @returns {import('svelte/store').Writable<T>}
 */
export const localStore = (key, initialValue) => {
  const storedValue = localStorage.getItem(key)
  let value = initialValue

  if (storedValue !== null) {
    if (typeof initialValue === 'number') {
      value = /** @type {T} */ (Number(storedValue))
    } else if (typeof initialValue === 'boolean') {
      value = /** @type {T} */ (storedValue === 'true')
    } else {
      value = /** @type {T} */ (storedValue)
    }
  }

  const store = writable(value)

  store.subscribe((value) => {
    localStorage.setItem(key, String(value))
  })

  return store
}

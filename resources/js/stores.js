import { readable, writable } from 'svelte/store';

const { context, user, icons, settings, plugins } = portalJson;

export const useAppStore = readable({
  baseUrl: smf_scripturl,
  loading: ajax_notification_text,
  sessionId: smf_session_id,
  sessionVar: smf_session_var,
});

export const useContextStore = readable(context);

export const useUserStore = readable(user);

export const useIconStore = readable(icons);

export const useSettingStore = readable(settings);

export const usePluginStore = readable(plugins);

export const useLocalStorage = (key, initialValue) => {
  const storedValue = localStorage.getItem(key);
  const initial = storedValue !== null ? storedValue : initialValue;
  const store = writable(initial);

  store.subscribe((value) => {
    localStorage.setItem(key, value.toString());
  });

  return store;
}

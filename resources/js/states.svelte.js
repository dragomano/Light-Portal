const { context, user, icons, settings, plugins } = portalJson;

export const appState = $state({
  baseUrl: window.smf_scripturl,
  loading: window.ajax_notification_text,
  sessionId: window.smf_session_id,
  sessionVar: window.smf_session_var,
});

export const contextState = $state(context);
export const userState = $state(user);
export const iconState = $state(icons);
export const settingState = $state(settings);
export const pluginState = $state(plugins);

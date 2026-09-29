import type { PluginState } from '../components/types'

const { context, user, icons, settings, plugins } = window.portalJson

export const postJson = (url: string, data: unknown) =>
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json; charset=utf-8' },
    body: JSON.stringify(data),
  })

export const appState = $state({
  baseUrl: window.smf_scripturl,
  loading: window.ajax_notification_text,
  sessionId: window.smf_session_id,
  sessionVar: window.smf_session_var,
})

export const contextState = $state(context)
export const userState = $state(user)
export const iconState = $state(icons)
export const settingState = $state(settings)
export const pluginState: PluginState = $state(plugins)

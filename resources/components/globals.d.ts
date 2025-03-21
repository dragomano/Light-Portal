declare global {
  interface Window {
    smf_scripturl: string;
    smf_default_theme_url: string;
    ajax_notification_text: string;
    smf_session_id: string;
    smf_session_var: string;
    portalJson: any;
    axios: typeof import("axios");
  }
}

export {};

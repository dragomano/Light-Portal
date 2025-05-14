import type AlpineType from 'alpinejs';

declare global {
  interface Window {
    smf_scripturl: string;
    smf_default_theme_url: string;
    ajax_notification_text: string;
    smf_session_id: string;
    smf_session_var: string;
    portalJson: any;
    Alpine: AlpineType;
    axios: typeof import("axios").default;
    loadExternalScript: (url: string, isModule?: boolean) => Promise<void>;
    loadPortalScript: (url: string, isModule?: boolean) => Promise<void>;
    usePortalApi: (endpoint: string, scriptName: string) => Promise<void>;
  }
}

export {};

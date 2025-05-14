import Alpine from 'alpinejs';
// @ts-ignore
import slug from 'alpinejs-slug';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.post['Content-Type'] = 'application/json; charset=utf-8';

Alpine.plugin(slug);
Alpine.start();

window.Alpine = Alpine;
window.portalJson = '';

window.loadExternalScript = (url, isModule = false) => {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');

    script.src = url;
    if (isModule) {
      script.type = 'module'
    }
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Script loading error: ${url}`));

    document.body.appendChild(script);
  });
}

window.loadPortalScript = (url, isModule = false) => {
  return window.loadExternalScript(window.smf_default_theme_url + '/scripts/light_portal/' + url, isModule);
}

window.usePortalApi = async (endpoint, scriptName) => {
  const response = await fetch(endpoint);
  window.portalJson = await response.json();

  return window.loadPortalScript(scriptName, true);
}

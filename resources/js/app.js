import Alpine from 'alpinejs';
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
  return loadExternalScript(smf_default_theme_url + '/scripts/light_portal/' + url, isModule);
}

window.usePortalApi = (endpoint, scriptName) => {
  return fetch(endpoint)
    .then(response => {
      return response.json();
    })
    .then(data => {
      window.portalJson = data;

      return loadPortalScript(scriptName, true);
    });
}

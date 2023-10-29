import Alpine from 'alpinejs';
import slug from 'alpinejs-slug';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.post['Content-Type'] = 'application/json; charset=utf-8';

Alpine.plugin(slug);
Alpine.start();

window.Alpine = Alpine;

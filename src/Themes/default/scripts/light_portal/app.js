import Alpine from 'alpinejs'
import slug from 'alpinejs-slug'
import styleNames from '@shat/stylenames'
import axios from 'axios'

window.styleNames = styleNames

window.axios = axios
window.axios.defaults.headers.post['Content-Type'] = 'application/json; charset=utf-8'

Alpine.plugin(slug)
Alpine.start()
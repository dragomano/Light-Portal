let mix = require('laravel-mix');

mix.js('Themes/default/scripts/light_portal/app.js', 'Themes/default/scripts/light_portal/alpine.min.js');

mix.copy('node_modules/@eastdesire/jscolor/jscolor.min.js', 'Themes/default/scripts/light_portal');
mix.copy('node_modules/sortablejs/Sortable.min.js', 'Themes/default/scripts/light_portal');
mix.copy('node_modules/tom-select/dist/js/tom-select.complete.min.js', 'Themes/default/scripts/light_portal');
mix.copy('node_modules/tom-select/dist/css/tom-select.min.css', 'Themes/default/css/light_portal');
mix.copy('node_modules/vanilla-lazyload/dist/lazyload.esm.min.js', 'Themes/default/scripts/light_portal');
mix.copy('node_modules/virtual-select-plugin/dist/virtual-select.min.css', 'Themes/default/css/light_portal');
mix.copy('node_modules/virtual-select-plugin/dist/virtual-select.min.js', 'Themes/default/scripts/light_portal');

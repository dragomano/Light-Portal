let mix = require('laravel-mix');

mix.js('src/Themes/default/scripts/light_portal/app.js', 'src/Themes/default/scripts/light_portal/bundle.min.js');

mix.copy('node_modules/@eastdesire/jscolor/jscolor.min.js', 'src/Themes/default/scripts/light_portal');
mix.copy('node_modules/sortablejs/Sortable.min.js', 'src/Themes/default/scripts/light_portal');
mix.copy('node_modules/tom-select/dist/js/tom-select.complete.min.js', 'src/Themes/default/scripts/light_portal');
mix.copy('node_modules/tom-select/dist/js/tom-select.complete.min.js.map', 'src/Themes/default/scripts/light_portal');
mix.copy('node_modules/tom-select/dist/css/tom-select.min.css', 'src/Themes/default/css/light_portal');
mix.copy('node_modules/tom-select/dist/css/tom-select.min.css.map', 'src/Themes/default/css/light_portal');
mix.copy('node_modules/vanilla-lazyload/dist/lazyload.esm.min.js', 'src/Themes/default/scripts/light_portal');
mix.copy('node_modules/virtual-select-plugin/dist/virtual-select.min.css', 'src/Themes/default/css/light_portal');
mix.copy('node_modules/virtual-select-plugin/dist/virtual-select.min.js', 'src/Themes/default/scripts/light_portal');

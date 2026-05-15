import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
  build: {
    outDir: resolve('./src/Themes/default/scripts/light_portal'),
    emptyOutDir: false,
    rolldownOptions: {
      input: 'resources/js/app.js',
      output: {
        entryFileNames: 'bundle.min.js',
        format: 'iife',
      },
    },
  },

  plugins: [
    viteStaticCopy({
      targets: [
        {
          src: 'node_modules/sortablejs/Sortable.min.js',
          dest: '',
          rename: {
            stripBase: 2,
          },
        },
        {
          src: 'node_modules/vanilla-lazyload/dist/lazyload.min.js',
          dest: '',
          rename: {
            stripBase: 3,
          },
        },
        {
          src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.js',
          dest: '',
          rename: {
            stripBase: 3,
          },
        },
        {
          src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.css',
          dest: '../../css/light_portal',
          rename: {
            stripBase: 3,
          },
        },
      ],
    }),
  ],
});

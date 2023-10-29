import { resolve } from 'path';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const dist = resolve('./src/Themes/default/scripts/light_portal');

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Themes/default/scripts/light_portal/app.js',
      output: {
        dir: dist,
        entryFileNames: 'bundle.min.js',
        format: 'iife',
      },
    },
  },
  plugins: [
    viteStaticCopy({
      targets: [
        { src: 'node_modules/@eastdesire/jscolor/jscolor.min.js', dest: dist },
        { src: 'node_modules/sortablejs/Sortable.min.js', dest: dist },
        { src: 'node_modules/vanilla-lazyload/dist/lazyload.esm.min.js', dest: dist },
        { src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.css', dest: dist },
        { src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.js', dest: dist },
      ],
    }),
  ],
});

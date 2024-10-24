import { resolve } from 'path';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const dist = resolve('./src/Themes/default/scripts/light_portal');
const cssDir = resolve('./src/Themes/default/css/light_portal');

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    outDir: dist,
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Themes/default/scripts/light_portal/app.js',
      output: {
        entryFileNames: 'bundle.min.js',
        format: 'iife',
      },
    },
  },
  plugins: [
    viteStaticCopy({
      targets: [
        { src: 'node_modules/sortablejs/Sortable.min.js', dest: dist },
        { src: 'node_modules/vanilla-lazyload/dist/lazyload.min.js', dest: dist },
        { src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.css', dest: cssDir },
        { src: 'node_modules/virtual-select-plugin/dist/virtual-select.min.js', dest: dist },
      ],
    }),
  ],
});

import { resolve } from 'path';
import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

const dist = resolve('./src/Themes/default/scripts/light_portal');

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    outDir: dist,
    emptyOutDir: false,
    rollupOptions: {
      input: 'resources/js/app_plugins.js',
      output: {
        entryFileNames: 'bundle_plugins.js',
        format: 'esm',
      },
    },
  },
  plugins: [
    svelte({
      emitCss: false,
    }),
  ],
});

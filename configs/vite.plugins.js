import { resolve } from 'path';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

const dist = resolve('./src/Themes/default/scripts/light_portal');

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    outDir: dist,
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Themes/default/scripts/light_portal/app_plugins',
      output: {
        entryFileNames: 'bundle_plugins.js',
        format: 'esm',
      },
    },
  },
  plugins: [vue()],
  resolve: {
    alias: {
      '@scripts': resolve('./src/Themes/default/scripts/light_portal/dev'),
    },
  },
});

import { resolve } from 'path';
import { defineConfig } from 'vite';

const dist = resolve('./src/Themes/default/css/light_portal');

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    outDir: dist,
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Themes/default/css/light_portal/sass/portal.scss',
      output: {
        assetFileNames: 'portal.css',
      },
    },
  },
});

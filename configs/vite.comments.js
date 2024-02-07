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
      external: ['vue', 'vue-i18n', 'pinia'],
      input: 'src/Themes/default/scripts/light_portal/app_comments.js',
      output: {
        entryFileNames: 'bundle_comments.js',
        format: 'esm',
        globals: { vue: 'Vue', 'vue-i18n': 'VueI18n', pinia: 'Pinia' },
      },
    },
  },
  plugins: [
    vue({
      template: {
        compilerOptions: {
          isCustomElement: (tag) => tag === 'markdown-toolbar' || tag.startsWith('md-'),
        },
      },
    }),
  ],
});

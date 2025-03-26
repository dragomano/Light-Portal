import { build } from 'vite';
import { resolve } from 'path';
import { svelte } from '@sveltejs/vite-plugin-svelte';

const dist = './src/Sources/LightPortal/Plugins';

const sharedConfig = {
  plugins: [
    svelte({
      emitCss: false
    }),
  ],
  build: {
    emptyOutDir: false
  },
};

async function buildMemory() {
  await build({
    ...sharedConfig,
    build: {
      ...sharedConfig.build,
      outDir: resolve(dist, 'Memory'),
      rollupOptions: {
        input: 'resources/js/apps/memory_plugin.js',
        output: {
          entryFileNames: 'memory.js',
        },
      },
    },
  });
};

async function buildAll() {
  await buildMemory();
};

buildAll().catch((err) => {
  console.error(err);
  process.exit(1);
});

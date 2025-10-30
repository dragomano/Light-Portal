import { build } from 'vite';
import { resolve } from 'path';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { viteStaticCopy } from 'vite-plugin-static-copy';

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
}

async function buildChessBoard() {
  await build({
    ...sharedConfig,
    build: {
      ...sharedConfig.build,
      outDir: resolve(dist, 'ChessBoard'),
      rollupOptions: {
        input: 'resources/js/apps/chessboard_plugin.js',
        output: {
          entryFileNames: 'chessboard.js',
          assetFileNames: (asset) => {
            const name = asset.names?.[0] || '';

            if (name.endsWith('.css')) return 'chessboard.css';

            return '[name][extname]';
          }
        },
      },
    },
    plugins: [
      ...sharedConfig.plugins,
      viteStaticCopy({
        targets: [
          { src: 'node_modules/cm-chessboard/assets/pieces/standard.svg', dest: resolve(dist, 'ChessBoard/images') },
          { src: 'node_modules/cm-chessboard/assets/pieces/staunty.svg', dest: resolve(dist, 'ChessBoard/images') },
          { src: 'node_modules/cm-chessboard/assets/extensions/markers/markers.svg', dest: resolve(dist, 'ChessBoard/images') },
          { src: 'node_modules/stockfish/src/stockfish-17.1-lite-single-03e3232.js', dest: resolve(dist, 'ChessBoard/stockfish') },
          { src: 'node_modules/stockfish/src/stockfish-17.1-lite-single-03e3232.wasm', dest: resolve(dist, 'ChessBoard/stockfish') },
        ],
      }),
    ],
  });
}

async function buildAll() {
  await buildMemory();
  await buildChessBoard();
}

buildAll().catch((err) => {
  console.error(err);
  process.exit(1);
});

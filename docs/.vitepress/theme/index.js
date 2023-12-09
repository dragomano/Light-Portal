import DefaultTheme from 'vitepress/theme';
import { useData, useRoute } from 'vitepress';
import codeblocksFold from 'vitepress-plugin-codeblocks-fold';
import 'vitepress-plugin-codeblocks-fold/style/index.scss';
import './custom.css';

export default {
  ...DefaultTheme,

  enhanceApp(ctx) {
    DefaultTheme.enhanceApp(ctx);
  },

  setup() {
    const { frontmatter } = useData();
    const route = useRoute();

    codeblocksFold({ route, frontmatter }, true, 400);
  },
};

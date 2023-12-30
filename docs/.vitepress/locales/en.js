export default {
  // replace with your native language name
  label: 'English',
  // replace with your native language code (https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469)
  lang: 'en',
  title: 'Light Portal Docs',
  description: 'Light Portal Online Documentation',
  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Introduction', link: '/intro' },
      { text: 'Team', link: '/team' },
      { text: 'Examples', link: '/examples' },
      { text: 'Demo', link: 'https://demo.dragomano.ru/' },
    ],
    outline: {
      label: 'On this page',
    },
    docFooter: {
      prev: 'Previous page',
      next: 'Next page',
    },
    darkModeSwitchLabel: 'Appearance',
    lightModeSwitchTitle: 'Switch to light theme',
    darkModeSwitchTitle: 'Switch to dark theme',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Back to top',
    langMenuLabel: 'Change language',
    notFound: {
      title: 'PAGE NOT FOUND',
      quote:
        "But if you don't change your direction, and if you keep looking, you may end up where you are heading.",
      linkLabel: 'go to home',
      linkText: 'Take me home',
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Search',
            buttonAriaLabel: 'Search',
          },
          modal: {
            displayDetails: 'Display detailed list',
            resetButtonTitle: 'Reset search',
            backButtonTitle: 'Close search',
            noResultsText: 'No results for',
            footer: {
              selectText: 'to select',
              navigateText: 'to navigate',
              closeText: 'to close',
            },
          },
        },
      },
    },
  },
};

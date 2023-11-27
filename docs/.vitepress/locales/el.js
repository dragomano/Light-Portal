export default {
  label: 'Ελληνικά',
  lang: 'el',
  title: 'Έγγραφα Light Portal',
  description: 'Light Portal Online Documentation',
  themeConfig: {
    nav: [
      { text: 'Κύριος', link: '/' },
      { text: 'Εισαγωγή', link: '/intro' },
      { text: 'Επίδειξη', link: 'https://demo.dragomano.ru/' },
    ],
    outline: {
      label: 'Содержание',
    },
    docFooter: {
      prev: 'Предыдущая страница',
      next: 'Следующая страница',
    },
    darkModeSwitchLabel: 'Оформление',
    sidebarMenuLabel: 'Меню',
    returnToTopLabel: 'Вернуться наверх',
    langMenuLabel: 'Изменить язык',
    search: {
      options: {
        locales: {
          ru: {
            translations: {
              button: {
                buttonText: 'Поиск',
                buttonAriaLabel: 'Поиск',
              },
              modal: {
                displayDetails: 'Отобразить подробный список',
                resetButtonTitle: 'Сбросить поиск',
                backButtonTitle: 'Закрыть поиск',
                noResultsText: 'Нет результатов для',
                footer: {
                  selectText: 'выбрать',
                  navigateText: 'перейти',
                  closeText: 'закрыть',
                },
              },
            },
          },
        },
      },
    },
  },
};

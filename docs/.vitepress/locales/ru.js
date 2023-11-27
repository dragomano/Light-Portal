export default {
  label: 'Русский',
  lang: 'ru',
  title: 'Документация Light Portal',
  description: 'Онлайн-документация Light Portal',
  themeConfig: {
    nav: [
      { text: 'Главная', link: '/' },
      { text: 'Введение', link: '/intro' },
      { text: 'Демка', link: 'https://demo.dragomano.ru/' },
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
    notFound: {
      title: 'СТРАНИЦА НЕ НАЙДЕНА',
      quote: 'Но если не менять направление и продолжать искать, то можно оказаться там, где надо.',
      linkLabel: 'перейти на главную',
      linkText: 'Хочу домой',
    },
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

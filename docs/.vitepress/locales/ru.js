export default {
  // replace with your native language name
  label: 'Русский',
  // replace with your native language code (https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469)
  lang: 'ru',
  title: 'Документация Light Portal',
  description: 'Онлайн-документация Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Главная',
        link: '/',
      },
      {
        text: 'Введение',
        link: '/intro',
      },
      { text: 'Команда', link: '/team' },
      { text: 'Примеры', link: '/examples' },
      {
        text: 'Демка',
        link: 'https://demo.dragomano.ru/',
      },
    ],
    outline: { label: 'Содержание этой страницы' },
    docFooter: {
      prev: 'Предыдущая страница',
      next: 'Следующая страница',
    },
    darkModeSwitchLabel: 'Оформление',
    sidebarMenuLabel: 'Меню',
    returnToTopLabel: 'Вернуться к началу',
    langMenuLabel: 'Изменить язык',
    notFound: {
      title: 'СТРАНИЦА НЕ НАЙДЕНА',
      quote: 'Но если не менять направление и продолжать искать, то можно оказаться там, где надо.',
      linkLabel: 'перейти на главную',
      linkText: 'Вернуться на главную',
    },
    search: {
      options: {
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
};

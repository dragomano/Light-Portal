export default {
  label: 'Українська',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  lang: 'uk',
  title: 'Документація Light Portal',
  description: 'Онлайн-документація Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Головна',
        link: '/'
      },
      {
        text: 'Вступ',
        link: '/intro'
      },
      {
        text: 'Приклади',
        link: '/examples'
      },
      {
        text: 'Список змін',
        link: '/changelog'
      }
    ],
    outline: { label: 'На цій сторінці' },
    docFooter: {
      prev: 'Попередня сторінка',
      next: 'Наступна сторінка'
    },
    darkModeSwitchLabel: 'Оформлення',
    lightModeSwitchTitle: 'Застосувати світлу тему',
    darkModeSwitchTitle: 'Застосувати темну тему',
    sidebarMenuLabel: 'Меню',
    returnToTopLabel: 'На початок',
    langMenuLabel: 'Змінити мову',
    notFound: {
      title: 'СТОРІНКА НЕ ЗНАЙДЕНО',
      quote: 'Але якщо ви не зміните напрямок і продовжите шукати, ви зможете потрапити туди, куди ви прямуєте.',
      linkLabel: 'повернутися на додому',
      linkText: 'Перейти на домашню сторінку'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Пошук',
            buttonAriaLabel: 'Пошук'
          },
          modal: {
            displayDetails: 'Відобразити детальний список',
            resetButtonTitle: 'Скинути пошук',
            backButtonTitle: 'Закрити пошук',
            noResultsText: 'Немає результатів для',
            footer: {
              selectText: 'щоб вибрати',
              navigateText: 'для навігації',
              closeText: 'закрити'
            }
          }
        }
      }
    }
  }
};
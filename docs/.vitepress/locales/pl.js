export default {
  label: 'Polski',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  lang: 'pl',
  title: 'Dokumentacja Light Portal',
  description: 'Dokumentacja online Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Strona główna',
        link: '/'
      },
      {
        text: 'Wprowadzanie',
        link: '/intro'
      },
      {
        text: 'Przykłady',
        link: '/examples'
      },
      {
        text: 'Lista zmian',
        link: '/changelog'
      }
    ],
    outline: { label: 'Na tej stronie' },
    docFooter: {
      prev: 'Poprzednia strona',
      next: 'Następna strona'
    },
    darkModeSwitchLabel: 'Wygląd',
    lightModeSwitchTitle: 'Przełącz na jasny motyw',
    darkModeSwitchTitle: 'Przełącz na ciemny motyw',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Powrót do góry',
    langMenuLabel: 'Zmień język',
    notFound: {
      title: 'STRONA NIEPRZEZNACZONA',
      quote: 'Ale jeśli nie zmienisz kierunku, a jeśli będziesz szukać, możesz skończyć tam, gdzie się kierujesz.',
      linkLabel: 'idź do domu',
      linkText: 'Zabierz mnie do domu'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Szukaj',
            buttonAriaLabel: 'Szukaj'
          },
          modal: {
            displayDetails: 'Wyświetl szczegółową listę',
            resetButtonTitle: 'Resetuj wyszukiwanie',
            backButtonTitle: 'Zamknij wyszukiwanie',
            noResultsText: 'Brak wyników dla',
            footer: {
              selectText: 'wybrać',
              navigateText: 'nawigować',
              closeText: 'zamknąć'
            }
          }
        }
      }
    }
  }
};
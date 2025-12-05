import { defineConfig } from 'vitepress';
export default defineConfig({
  lang: 'de',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  description: 'Light Portal Online-Dokumentation',
  themeConfig: {
    nav: [
      {
        text: 'Zuhause',
        link: '/'
      },
      {
        text: 'Einführung',
        link: '/intro'
      },
      {
        text: 'Beispiele',
        link: '/examples'
      },
      {
        text: 'Changelog',
        link: '/changelog'
      }
    ],
    outline: { label: 'Auf dieser Seite' },
    docFooter: {
      prev: 'Vorherige Seite',
      next: 'Nächste Seite'
    },
    darkModeSwitchLabel: 'Erscheinungsbild',
    lightModeSwitchTitle: 'Zum hellen Theme wechseln',
    darkModeSwitchTitle: 'Zum dunklen Theme wechseln',
    sidebarMenuLabel: 'Menü',
    returnToTopLabel: 'Zurück nach oben',
    langMenuLabel: 'Sprache ändern',
    lastUpdatedText: 'Last updated',
    notFound: {
      title: 'SEITE NICHT GEFUNDEN',
      quote: 'Aber wenn man seine Richtung nicht ändert, und wenn man weiter sucht, kann man am Ende wohin man sich bewegt.',
      linkLabel: 'nach Hause gehen',
      linkText: 'Nimm mich nach Hause'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Suchen',
            buttonAriaLabel: 'Suchen'
          },
          modal: {
            displayDetails: 'Detaillierte Liste anzeigen',
            resetButtonTitle: 'Suche zurücksetzen',
            backButtonTitle: 'Suche schließen',
            noResultsText: 'Keine Ergebnisse für',
            footer: {
              selectText: 'auswählen',
              navigateText: 'navigieren',
              closeText: 'schließen'
            }
          }
        }
      }
    }
  }
});
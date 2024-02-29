export default {
  label: 'Italiano',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  lang: 'it',
  title: 'Documentazione Light Portal',
  description: 'Documentazione Online Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Inizio',
        link: '/'
      },
      {
        text: 'Introduzione',
        link: '/intro'
      },
      {
        text: 'Team',
        link: '/team'
      },
      {
        text: 'Esempi',
        link: '/examples'
      },
      {
        text: 'Demo',
        link: 'https://demo.dragomano.ru/'
      },
      {
        text: 'Changelog',
        link: '/changelog'
      }
    ],
    outline: { label: 'In questa pagina' },
    docFooter: {
      prev: 'Pagina precedente',
      next: 'Pagina successiva'
    },
    darkModeSwitchLabel: 'Aspetto',
    lightModeSwitchTitle: 'Passa al tema chiaro',
    darkModeSwitchTitle: 'Passa al tema scuro',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Torna in cima',
    langMenuLabel: 'Cambia lingua',
    notFound: {
      title: 'PAGINA NON TROVATA',
      quote: 'Ma se non cambi direzione e se continui a guardare, potresti finire dove stai andando.',
      linkLabel: 'Vai alla home',
      linkText: 'Vai alla Home'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Cerca',
            buttonAriaLabel: 'Cerca'
          },
          modal: {
            displayDetails: 'Visualizza lista dettagliata',
            resetButtonTitle: 'Azzera la ricerca',
            backButtonTitle: 'Chiudi ricerca',
            noResultsText: 'Nessun risultato per',
            footer: {
              selectText: 'seleziona',
              navigateText: 'naviga',
              closeText: 'chiudi'
            }
          }
        }
      }
    }
  }
};
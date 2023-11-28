export default {
  // replace with your native language name
  label: 'Italiano',
  // replace with your native language code (https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469)
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
        text: 'Demo',
        link: 'https://demo.dragomano.ru/'
      }
    ],
    outline: { label: 'In questa pagina' },
    docFooter: {
      prev: 'Pagina precedente',
      next: 'Pagina successiva'
    },
    darkModeSwitchLabel: 'Aspetto',
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
              selectText: 'per selezionare',
              navigateText: 'per navigare',
              closeText: 'per chiudere'
            }
          }
        }
      }
    }
  }
};
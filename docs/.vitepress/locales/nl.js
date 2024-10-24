export default {
  label: 'Dutch',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  lang: 'nl',
  title: 'Light Portal Documenten',
  description: 'Light Portal Online Documentatie',
  themeConfig: {
    nav: [
      {
        text: 'Startpagina',
        link: '/'
      },
      {
        text: 'Introductie',
        link: '/intro'
      },
      {
        text: 'Voorbeelden',
        link: '/examples'
      },
      {
        text: 'Demo',
        link: 'https://demo.dragomano.ru/'
      },
      {
        text: 'Wijzigingslogboek',
        link: '/changelog'
      }
    ],
    outline: { label: 'Op deze pagina' },
    docFooter: {
      prev: 'Vorige pagina',
      next: 'Volgende pagina'
    },
    darkModeSwitchLabel: 'Uiterlijk',
    lightModeSwitchTitle: 'Overschakelen naar licht thema',
    darkModeSwitchTitle: 'Overschakelen naar donker thema',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Terug naar boven',
    langMenuLabel: 'Taal wijzigen',
    notFound: {
      title: 'PAGINA NIET GEVONDEN',
      quote: 'Maar als je de richting niet verandert, en als je blijft kijken, kun je eindigen waar je naartoe gaat.',
      linkLabel: 'ga naar huis',
      linkText: 'Breng me naar huis'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Zoeken',
            buttonAriaLabel: 'Zoeken'
          },
          modal: {
            displayDetails: 'Gedetailleerde lijst weergeven',
            resetButtonTitle: 'Reset zoekopdracht',
            backButtonTitle: 'Zoekopdracht sluiten',
            noResultsText: 'Geen resultaten voor',
            footer: {
              selectText: 'te selecteren',
              navigateText: 'Om te navigeren',
              closeText: 'te sluiten'
            }
          }
        }
      }
    }
  }
};
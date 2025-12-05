import { defineConfig } from 'vitepress';
export default defineConfig({
  lang: 'sl',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  description: 'Spletna dokumentacija za Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Domov',
        link: '/'
      },
      {
        text: 'Predstavitev',
        link: '/intro'
      },
      {
        text: 'Primeri',
        link: '/examples'
      },
      {
        text: 'Dnevnik sprememb',
        link: '/changelog'
      }
    ],
    outline: { label: 'Na tej strani' },
    docFooter: {
      prev: 'Prejšnja stran',
      next: 'Naslednja stran'
    },
    darkModeSwitchLabel: 'Videz',
    lightModeSwitchTitle: 'Preklopi na svetlo temo',
    darkModeSwitchTitle: 'Preklopi na temno temo',
    sidebarMenuLabel: 'Meni',
    returnToTopLabel: 'Nazaj na vrh',
    langMenuLabel: 'Spremeni jezik',
    lastUpdatedText: 'Nazadnje posodobljeno',
    notFound: {
      title: 'STRAN NI NAJDENA',
      quote: 'Ampak če ne spremeniš svoje smeri in če nadaljuješ z iskanjem, lahko končaš tam, kamor si namenjen.',
      linkLabel: 'pojdi na začetno stran',
      linkText: 'Vrni me domov'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Išči',
            buttonAriaLabel: 'Išči'
          },
          modal: {
            displayDetails: 'Prikaži podroben seznam',
            resetButtonTitle: 'Ponastavi iskanje',
            backButtonTitle: 'Zapri iskanje',
            noResultsText: 'Ni rezultatov za',
            footer: {
              selectText: 'izbrati',
              navigateText: 'navigirati',
              closeText: 'zapreti'
            }
          }
        }
      }
    }
  }
});
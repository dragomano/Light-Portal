import { defineConfig } from 'vitepress';
export default defineConfig({
  lang: 'fr',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  description: 'Documentation en ligne Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Domicile',
        link: '/'
      },
      {
        text: 'Introduction',
        link: '/intro'
      },
      {
        text: 'Exemples',
        link: '/examples'
      },
      {
        text: 'Changelog',
        link: '/changelog'
      }
    ],
    outline: { label: 'Sur cette page' },
    docFooter: {
      prev: 'Page précédente',
      next: 'Page suivante'
    },
    darkModeSwitchLabel: 'Apparence',
    lightModeSwitchTitle: 'Basculer vers le thème clair',
    darkModeSwitchTitle: 'Basculer vers le thème sombre',
    sidebarMenuLabel: 'Menu',
    returnToTopLabel: 'Retour en haut',
    langMenuLabel: 'Changer de langue',
    notFound: {
      title: 'PAGE NON TROUVÉ',
      quote: 'Mais si vous ne changez pas de direction et si vous continuez à regarder, vous finirez peut-être par vous diriger.',
      linkLabel: 'aller à la page d\'accueil',
      linkText: 'Ramenez-moi à la maison'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Chercher',
            buttonAriaLabel: 'Chercher'
          },
          modal: {
            displayDetails: 'Afficher la liste détaillée',
            resetButtonTitle: 'Réinitialiser la recherche',
            backButtonTitle: 'Fermer la recherche',
            noResultsText: 'Aucun résultat pour',
            footer: {
              selectText: 'pour sélectionner',
              navigateText: 'pour naviguer',
              closeText: 'fermer'
            }
          }
        }
      }
    }
  }
});
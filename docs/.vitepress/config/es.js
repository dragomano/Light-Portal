import { defineConfig } from 'vitepress';
export default defineConfig({
  lang: 'es',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  description: 'Documentación en línea Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'Inicio',
        link: '/'
      },
      {
        text: 'Introducción',
        link: '/intro'
      },
      {
        text: 'Ejemplos',
        link: '/examples'
      },
      {
        text: 'Changelog',
        link: '/changelog'
      }
    ],
    outline: { label: 'En esta página' },
    docFooter: {
      prev: 'Página anterior',
      next: 'Página siguiente'
    },
    darkModeSwitchLabel: 'Apariencia',
    lightModeSwitchTitle: 'Cambiar a tema claro',
    darkModeSwitchTitle: 'Cambiar a tema oscuro',
    sidebarMenuLabel: 'Menú',
    returnToTopLabel: 'Volver arriba',
    langMenuLabel: 'Cambiar idioma',
    notFound: {
      title: 'PAGO NO FUNCIONADO',
      quote: 'Pero si no cambias de dirección, y si sigues mirando, puede que termines adonde vayas.',
      linkLabel: 'ir a casa',
      linkText: 'Llévame a casa'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Buscar',
            buttonAriaLabel: 'Buscar'
          },
          modal: {
            displayDetails: 'Mostrar lista detallada',
            resetButtonTitle: 'Restablecer búsqueda',
            backButtonTitle: 'Cerrar búsqueda',
            noResultsText: 'Sin resultados para',
            footer: {
              selectText: 'seleccionar',
              navigateText: 'para navegar',
              closeText: 'cerrar'
            }
          }
        }
      }
    }
  }
});
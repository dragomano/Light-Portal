export default {
  label: 'Türkçe',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  lang: 'tr',
  title: 'Light Portal Belgeleri',
  description: 'Light Portal Çevrimiçi Dokümantasyonu',
  themeConfig: {
    nav: [
      {
        text: 'Ana Sayfa',
        link: '/'
      },
      {
        text: 'Giriş',
        link: '/intro'
      },
      {
        text: 'Örnekler',
        link: '/examples'
      },
      {
        text: 'Değişiklik günlüğü',
        link: '/changelog'
      }
    ],
    outline: { label: 'Bu sayfada' },
    docFooter: {
      prev: 'Önceki sayfa',
      next: 'Sonraki sayfa'
    },
    darkModeSwitchLabel: 'görünüm',
    lightModeSwitchTitle: 'Açık tema geçişi',
    darkModeSwitchTitle: 'Koyu tema geçişi',
    sidebarMenuLabel: 'Menü',
    returnToTopLabel: 'Başa dön',
    langMenuLabel: 'Dili değiştir',
    notFound: {
      title: 'SAYFA BULUNAMADI',
      quote: 'Ama eğer yönünü değiştirmezsen ve aramaya devam edersen, gittiğin yere varabilirsin.',
      linkLabel: 'ana sayfaya git',
      linkText: 'Beni eve götür'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Ara',
            buttonAriaLabel: 'Ara'
          },
          modal: {
            displayDetails: 'Ayrıntılı listeyi göster',
            resetButtonTitle: 'Aramayı sıfırla',
            backButtonTitle: 'Aramayı kapat',
            noResultsText: 'Sonuç yok',
            footer: {
              selectText: 'seçmek için',
              navigateText: 'gezmek için',
              closeText: 'kapatmak için'
            }
          }
        }
      }
    }
  }
};
import { defineConfig } from 'vitepress';
export default defineConfig({
  lang: 'ar',
  // https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469
  description: 'الوثائق عبر الإنترنت Light Portal',
  themeConfig: {
    nav: [
      {
        text: 'المنزل',
        link: '/'
      },
      {
        text: 'مقدمة',
        link: '/intro'
      },
      {
        text: 'أمثلة',
        link: '/examples'
      },
      {
        text: 'تغيير',
        link: '/changelog'
      }
    ],
    outline: { label: 'في هذه الصفحة' },
    docFooter: {
      prev: 'الصفحة السابقة',
      next: 'الصفحة التالية'
    },
    darkModeSwitchLabel: 'المظهر',
    lightModeSwitchTitle: 'التبديل إلى موضوع الضوء',
    darkModeSwitchTitle: 'التبديل إلى السمة المظلمة',
    sidebarMenuLabel: 'القائمة',
    returnToTopLabel: 'العودة إلى الأعلى',
    langMenuLabel: 'تغيير اللغة',
    lastUpdatedText: 'Last updated',
    notFound: {
      title: 'لا يوجد ملف',
      quote: 'ولكن إذا لم تغير اتجاهك ، وإذا استمرت في البحث ، قد ينتهي بك الأمر إلى أين تتجه .',
      linkLabel: 'الذهاب إلى المنزل',
      linkText: 'خذني إلى المنزل'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'البحث',
            buttonAriaLabel: 'البحث'
          },
          modal: {
            displayDetails: 'عرض قائمة مفصلة',
            resetButtonTitle: 'إعادة تعيين البحث',
            backButtonTitle: 'إغلاق البحث',
            noResultsText: 'لا توجد نتائج لـ',
            footer: {
              selectText: 'لتحديد',
              navigateText: 'للتنقل',
              closeText: 'أن يغلق'
            }
          }
        }
      }
    }
  }
});
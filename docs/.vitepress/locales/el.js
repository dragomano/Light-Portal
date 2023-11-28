export default {
  // replace with your native language name
  label: 'Αγγλικά',
  // replace with your native language code (https://gist.github.com/Josantonius/b455e315bc7f790d14b136d61d9ae469)
  lang: 'el',
  title: 'Έγγραφα Light Portal',
  description: 'Light Portal Online Τεκμηρίωση',
  themeConfig: {
    nav: [
      {
        text: 'Αρχική',
        link: '/'
      },
      {
        text: 'Εισαγωγή',
        link: '/intro'
      },
      {
        text: 'Επίδειξη',
        link: 'https://demo.dragomano.ru/'
      }
    ],
    outline: { label: 'Σε αυτήν την σελίδα' },
    docFooter: {
      prev: 'Προηγούμενη σελίδα',
      next: 'Επόμενη σελίδα'
    },
    darkModeSwitchLabel: 'Εμφάνιση',
    sidebarMenuLabel: 'Μενού',
    returnToTopLabel: 'Επιστροφή στην κορυφή',
    langMenuLabel: 'Αλλαγή γλώσσας',
    notFound: {
      title: 'Η ΣΕΛΙΔΑ ΔΕΝ ΒΡΕΘΗΚΕ',
      quote: 'Αλλά αν δεν αλλάξετε κατεύθυνση και αν συνεχίσετε να ψάχνετε, μπορεί να καταλήξετε εκεί που πηγαίνετε.',
      linkLabel: 'πήγαινε στην αρχική',
      linkText: 'Πήγαινε με στην αρχική'
    },
    search: {
      options: {
        translations: {
          button: {
            buttonText: 'Αναζήτηση',
            buttonAriaLabel: 'Αναζήτηση'
          },
          modal: {
            displayDetails: 'Εμφάνιση λεπτομερούς λίστας',
            resetButtonTitle: 'Επαναφορά αναζήτησης',
            backButtonTitle: 'Κλείσιμο αναζήτησης',
            noResultsText: 'Δεν υπάρχουν αποτελέσματα για',
            footer: {
              selectText: 'επέλεξε',
              navigateText: 'να πλοηγηθείς',
              closeText: 'να κλείσεις'
            }
          }
        }
      }
    }
  }
};
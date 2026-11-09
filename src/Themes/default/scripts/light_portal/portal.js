class PortalEntity {
  toggleSpin(target) {
    target.classList.toggle('fa-spin');
  }

  post(target) {
    const formElements = target.elements;

    for (let i = 0; i < formElements.length; i++) {
      if (
        (formElements[i].required && formElements[i].value === '') ||
        !formElements[i].checkValidity()
      ) {
        const tab = formElements[i].closest('section').dataset.content;
        const nav = target.querySelector(`[data-tab=${tab}]`);

        localStorage.removeItem('tab');

        nav.click();
      }
    }
  }
}

class Block extends PortalEntity {
  add(target) {
    const thisForm = document.forms['block_add_form'];

    thisForm['add_block'].value = target.dataset.type;
    thisForm.submit();
  }

  sort(e) {
    const items = e.to.children;

    let priority = [];

    for (let i = 0; i < items.length; i++) {
      const id = items[i].querySelector('.handle')
        ? parseInt(items[i].querySelector('.handle').parentNode.parentNode.dataset.id, 10)
        : null;

      if (id !== null) priority.push(id);
    }

    const source = e.from.dataset.placement || '';
    const target = e.to.dataset.placement || '';

    const sortIds = document.getElementById('lp_block_sort_ids');
    const sortPlacement = document.getElementById('lp_block_sort_placement');
    const sortSource = document.getElementById('lp_block_sort_source');

    sortIds.value = priority.join(',');
    sortPlacement.value = source === target ? '' : target;
    sortSource.value = source;

    [sortIds, sortPlacement, sortSource].forEach((input) => {
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });

    document.getElementById('lp_block_sort_trigger').click();

    const nextElem = e.item.nextElementSibling;
    const prevElem = e.item.previousElementSibling;

    if (nextElem && nextElem.className === 'windowbg centertext') {
      nextElem.remove();
    } else if (prevElem && prevElem.className === 'windowbg centertext') {
      prevElem.remove();
    }
  }
}

class Page extends PortalEntity {
  add(target) {
    const thisForm = document.forms['page_add_form'];

    thisForm['add_page'].value = target.dataset.type;
    thisForm.submit();
  }
}

class Category extends PortalEntity {
  sort(e) {
    const items = e.to.children;

    let priority = [];

    for (let i = 0; i < items.length; i++) {
      const id = items[i].querySelector('.handle')
        ? parseInt(items[i].querySelector('.handle').closest('div').dataset.id, 10)
        : null;

      if (id !== null) priority.push(id);
    }

    const sortIds = document.getElementById('lp_category_sort_ids');

    sortIds.value = priority.join(',');
    sortIds.dispatchEvent(new Event('input', { bubbles: true }));
    document.getElementById('lp_category_sort_trigger').click();
  }
}

class Tag extends PortalEntity {}

class PortalTabs {
  #refs = null;

  constructor(selector = '.lp_tabs') {
    this.#refs = {
      navigation: document.querySelector(`${selector} [data-navigation]`),
      content: document.querySelector(`${selector} [data-content]`),
    };

    this.#refs.navigation.addEventListener('click', this.#onChangeNavigation.bind(this));

    this.#restoreActiveTab();
  }

  #restoreActiveTab() {
    const el = this.#refs.navigation;
    const tab = localStorage.getItem('tab') || 'common';
    const nav = el.querySelector(`[data-tab=${tab}]`);

    if (nav) nav.click();
  }

  #onChangeNavigation({ target }) {
    if (!['DIV', 'I'].includes(target.nodeName) || target.hasAttribute('data-navigation')) return;

    const currentButton = target.nodeName === 'I' ? target.parentNode : target;

    localStorage.setItem('tab', currentButton.dataset.tab);

    this.#removeActiveClasses();
    this.#addActiveClasses(currentButton);
  }

  #removeActiveClasses() {
    const prevActiveButton = this.#refs.navigation.querySelector('.active_navigation');
    const prevActiveContent = this.#refs.content.querySelector('.active_content');

    if (prevActiveButton) {
      prevActiveButton.classList.remove('active_navigation');
      prevActiveContent.classList.remove('active_content');
    }
  }

  #addActiveClasses(currentButton) {
    const currentTab = currentButton.dataset.tab;
    const currentContent = this.#refs.content.querySelector(`[data-content=${currentTab}]`);

    currentButton.classList.add('active_navigation');
    currentContent.classList.add('active_content');
  }
}

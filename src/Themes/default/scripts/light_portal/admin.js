class PortalEntity {
  constructor() {
    this.workUrl = smf_scripturl + '?action=admin';
  }

  toggleSpin(target) {
    target.classList.toggle('fa-spin');
  }

  async toggleStatus(target) {
    const item = target.dataset.id;

    if (!item) return false;

    await axios.post(this.workUrl, {
      toggle_item: item,
    });
  }

  async remove(target) {
    if (!confirm(smf_you_sure)) return false;

    const item = target.dataset.id;

    if (!item) return false;

    await axios.post(this.workUrl, {
      delete_item: item,
    });

    target.closest('tr').remove();
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

        if (formElements[i].name.startsWith('title_')) {
          document.querySelector(`[data-name=${formElements[i].name}]`).click();
        }
      }
    }
  }
}

class Block extends PortalEntity {
  constructor() {
    super();
    this.workUrl = smf_scripturl + '?action=admin;area=lp_blocks;actions';
  }

  async clone(target) {
    const item = target.dataset.id;

    if (!item) return false;

    const response = await axios.post(this.workUrl, {
      clone_block: item,
    });

    const { success, id } = response.data;

    if (success) {
      const div = target.cloneNode(true);

      div.dataset.id = id;
      target.after(div);
    }
  }

  add(target) {
    const thisForm = document.forms['block_add_form'];

    thisForm['add_block'].value = target.dataset.type;
    thisForm.submit();
  }

  async sort(e) {
    const items = e.from.children;
    const items2 = e.to.children;

    let priority = [];
    let placement = '';

    for (let i = 0; i < items2.length; i++) {
      const key = items2[i].querySelector('.handle')
        ? parseInt(items2[i].querySelector('.handle').parentNode.parentNode.dataset.id, 10)
        : null;
      const place = items[i] && items[i].parentNode ? items[i].parentNode.dataset.placement : null;
      const place2 =
        items2[i] && items2[i].parentNode ? items2[i].parentNode.dataset.placement : null;

      if (place !== place2) placement = place2;

      if (key !== null) priority.push(key);
    }

    await axios.post(this.workUrl, {
      update_priority: priority,
      update_placement: placement,
    });

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
  constructor() {
    super();
    this.workUrl = smf_scripturl + '?action=admin;area=lp_pages;actions';
  }

  add(target) {
    const thisForm = document.forms['page_add_form'];

    thisForm['add_page'].value = target.dataset.type;
    thisForm.submit();
  }
}

class Category extends PortalEntity {
  constructor() {
    super();
    this.workUrl = smf_scripturl + '?action=admin;area=lp_categories;actions';
  }

  async updatePriority(e) {
    const items = e.to.children;

    let priority = [];

    for (let i = 0; i < items.length; i++) {
      const id = items[i].querySelector('.handle')
        ? parseInt(items[i].querySelector('.handle').closest('div').dataset.id, 10)
        : null;

      if (id !== null) priority.push(id);
    }

    await axios.post(this.workUrl, {
      update_priority: priority,
    });
  }
}

class Tag extends PortalEntity {
  constructor() {
    super();
    this.workUrl = smf_scripturl + '?action=admin;area=lp_tags;actions';
  }
}

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

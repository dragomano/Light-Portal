function handleDragging() {
  if (typeof dragula === 'undefined') {
    return
  }

  const drake = dragula([
    document.querySelector('[data-panel=header]'),
    document.querySelector('[data-panel=top]'),
    document.querySelector('[data-panel=left]'),
    document.querySelector('[data-panel=right]'),
    document.querySelector('[data-panel=bottom]'),
    document.querySelector('[data-panel=footer]')
  ], {
    moves: function (el, container, handle) {
      return handle.classList.contains('fa-sliders');
    }
  });

  drake.on('drag', function () {
    const containers = document.querySelectorAll('[data-panel]');
    for (let i = 0; i < containers.length; i++) {
      containers[i].classList.add('gu-over');
    }
  });

  drake.on('dragend', function () {
    const containers = document.querySelectorAll('[data-panel]');
    for (let i = 0; i < containers.length; i++) {
      containers[i].classList.remove('gu-over');
    }
  });

  drake.on('drop', function (el, target) {
    const panel = target.dataset.panel
    const ids = Array.from(target.children).map(item => +item.id.replace('block_', ''))

    doDrag(ids, panel)
  })

  const doDrag = async (ids, panel) => {
    const workUrl = smf_scripturl + '?action=admin;area=lp_blocks;actions';

    await axios.post(workUrl, {
      update_priority: ids,
      update_placement: panel,
    });
  }
}

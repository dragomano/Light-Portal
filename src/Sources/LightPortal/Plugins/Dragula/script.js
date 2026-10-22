function handleDragging() {
  if (typeof dragula === 'undefined') {
    return
  }

  const slots = document.querySelector('.lp_dragula_slots')

  // Both the panels rendered on the page and the dashed drop zones take part in dragging
  const containers = [...document.querySelectorAll('[data-panel]')]

  const drake = dragula(containers, {
    moves: function (el, container, handle) {
      return handle.classList.contains('fa-sliders');
    }
  });

  // A block can be wrapped by the panel direction, so the blocks are searched at any depth
  const blocksIn = container => [...container.querySelectorAll('aside[id^="block_"]')]

  // A zone that received a block must stay visible, otherwise the block cannot be dragged out again
  const syncSlots = () => {
    if (!slots) {
      return
    }

    let hasFilledSlot = false

    slots.querySelectorAll('.lp_dragula_slot').forEach((slot) => {
      const isFilled = blocksIn(slot).length > 0

      slot.classList.toggle('lp_dragula_slot_filled', isFilled)
      hasFilledSlot = hasFilledSlot || isFilled
    })

    slots.classList.toggle('lp_dragula_slots_pinned', hasFilledSlot)
  }

  const saveOrder = (ids, panel, source) => {
    const sortIds = document.getElementById('lp_dragula_sort_ids')
    const sortPlacement = document.getElementById('lp_dragula_sort_placement')
    const sortSource = document.getElementById('lp_dragula_sort_source')
    const trigger = document.getElementById('lp_dragula_sort_trigger')

    if (!sortIds || !sortPlacement || !sortSource || !trigger) {
      console.error('Dragula: sort controls are missing, the new block order was not saved')
      return
    }

    sortIds.value = ids.join(',')
    sortPlacement.value = panel
    sortSource.value = source

    ;[sortIds, sortPlacement, sortSource].forEach((input) => {
      input.dispatchEvent(new Event('input', { bubbles: true }))
    })

    trigger.click()
  }

  drake.on('drag', function () {
    const panels = document.querySelectorAll('[data-panel]')
    for (let i = 0; i < panels.length; i++) {
      panels[i].classList.add('gu-over')
    }

    document.body.classList.add('lp_dragula_dragging')
  });

  drake.on('dragend', function () {
    const panels = document.querySelectorAll('[data-panel]')
    for (let i = 0; i < panels.length; i++) {
      panels[i].classList.remove('gu-over')
    }

    document.body.classList.remove('lp_dragula_dragging')

    syncSlots()
  });

  drake.on('drop', function (el, target, source) {
    const panel = target.dataset.panel

    // When the panel is rendered on the page, the block is moved into it instead of staying in the zone
    const panelElement = target.classList.contains('lp_dragula_slot')
      ? document.querySelector(`[data-panel="${panel}"]:not(.lp_dragula_slot)`)
      : null

    if (panelElement) {
      panelElement.appendChild(el)
    }

    syncSlots()

    saveOrder(blocksIn(panelElement || target).map(item => +item.id.replace('block_', '')), panel, source.dataset.panel)
  });
}

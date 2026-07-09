type Action = 'bold' | 'italic' | 'quote' | 'code' | 'link' | 'image' | 'list' | 'task'

const wrappers: Record<Action, [string, string]> = {
  bold: ['**', '**'],
  italic: ['_', '_'],
  quote: ['> ', ''],
  code: ['`', '`'],
  link: ['[', '](url)'],
  image: ['![', '](url)'],
  list: ['- ', ''],
  task: ['- [ ] ', ''],
}

export function applyMarkdown(textarea: HTMLTextAreaElement, action: Action): void {
  const [prefix, suffix] = wrappers[action]
  const { selectionStart: start, selectionEnd: end, value } = textarea
  const selected = value.slice(start, end)
  const hasSelection = selected.length > 0

  // Check if the selection is already wrapped — if so, unwrap (toggle off)
  const before = value.slice(0, start)
  const after = value.slice(end)

  // Check both cases: wrapping is outside the selection or inside it (selection includes the markers)
  const wrappedOutside =
    suffix.length > 0 ? before.endsWith(prefix) && after.startsWith(suffix) : before.endsWith(prefix)
  const wrappedInside =
    suffix.length > 0 ? selected.startsWith(prefix) && selected.endsWith(suffix) : selected.startsWith(prefix)
  const alreadyWrapped = wrappedOutside || wrappedInside

  if (alreadyWrapped && suffix.length > 0) {
    // Remove wrapping prefix and suffix
    let cleanText: string
    let newStart: number

    if (wrappedOutside) {
      cleanText = selected
      textarea.value = value.slice(0, start - prefix.length) + cleanText + value.slice(end + suffix.length)
      newStart = start - prefix.length
    } else {
      // wrappedInside: selection includes the markers
      cleanText = selected.slice(prefix.length, selected.length - suffix.length)
      textarea.value = before + cleanText + after
      newStart = start
    }

    textarea.selectionStart = newStart
    textarea.selectionEnd = newStart + cleanText.length
  } else if (alreadyWrapped && suffix.length === 0) {
    // Remove line prefix (quote, list, task)
    let cleanText: string
    let newStart: number

    if (wrappedOutside) {
      cleanText = selected
      textarea.value = before.slice(0, before.length - prefix.length) + cleanText + after
      newStart = start - prefix.length
    } else {
      cleanText = selected.slice(prefix.length)
      textarea.value = before + cleanText + after
      newStart = start
    }

    textarea.selectionStart = newStart
    textarea.selectionEnd = newStart + cleanText.length
  } else if (hasSelection) {
    // Wrap selected text
    textarea.value = before + prefix + selected + suffix + after
    textarea.selectionStart = start + prefix.length
    textarea.selectionEnd = start + prefix.length + selected.length
  } else {
    // No selection — insert prefix+suffix and place cursor between them
    textarea.value = before + prefix + suffix + after
    textarea.selectionStart = start + prefix.length
    textarea.selectionEnd = start + prefix.length
  }

  textarea.dispatchEvent(new Event('input'))
  textarea.focus()
}

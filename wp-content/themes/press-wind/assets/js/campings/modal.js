function initFDHPAModal() {
  let lastFocusedEl = null

  function openModal(modalId) {
    const modal = document.getElementById(modalId)
    if (!modal) return

    lastFocusedEl = document.activeElement

    modal.classList.add('is-open')
    modal.setAttribute('aria-hidden', 'false')
    document.body.classList.add('fdhpa-modal-lock')

    const closeBtn = modal.querySelector('[data-modal-close]')
    if (closeBtn) closeBtn.focus()

    modal.dispatchEvent(new CustomEvent('fdhpa:modal:opened', { bubbles: true }))
  }

  function closeModal(modal) {
    modal.classList.remove('is-open')
    modal.setAttribute('aria-hidden', 'true')
    document.body.classList.remove('fdhpa-modal-lock')

    modal.dispatchEvent(new CustomEvent('fdhpa:modal:closed', { bubbles: true }))

    if (lastFocusedEl && lastFocusedEl.focus) {
      lastFocusedEl.focus()
    }
  }

  // Open modal
  document.addEventListener('click', function (e) {
    const opener = e.target.closest('[data-modal-open]')
    if (!opener) return

    e.preventDefault()
    openModal(opener.getAttribute('data-modal-open'))
  })

  // Close modal
  document.addEventListener('click', function (e) {
    const closer = e.target.closest('[data-modal-close]')
    if (!closer) return

    const modal = closer.closest('.fdhpa-modal')
    if (!modal) return

    e.preventDefault()
    closeModal(modal)
  })

  // ESC
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return

    const modal = document.querySelector('.fdhpa-modal.is-open')
    if (!modal) return

    closeModal(modal)
  })
}
export default initFDHPAModal
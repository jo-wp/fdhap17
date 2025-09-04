function generateSummary() {
  const content = document.querySelector('.single-content')
  const tocContainer = document.querySelector(
    '.single-sidebar__content__summary',
  )

  if (!content || !tocContainer) return

  // Récupère tous les H2
  const headings = content.querySelectorAll('h2')

  if (headings.length === 0) return

  // Crée une liste
  const ul = document.createElement('ul')
  ul.className = 'list-disc pl-4 space-y-2' // Tailwind pour un sommaire propre

  headings.forEach((h2, index) => {
    // Ajoute un id unique si pas déjà présent
    if (!h2.id) {
      h2.id = 'section-' + (index + 1)
    }

    // Crée un lien
    const li = document.createElement('li')
    const a = document.createElement('a')
    a.href = '#' + h2.id
    a.textContent = h2.textContent
    a.className = 'text-blue-600 hover:underline'

    li.appendChild(a)
    ul.appendChild(li)
  })

  // Injecte la liste dans le sommaire
  tocContainer.appendChild(ul)
}
export default generateSummary;
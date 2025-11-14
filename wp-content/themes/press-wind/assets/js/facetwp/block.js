export default function blockSearch() {
  const SPLIDE_KEY = '_splideInstance'
  const SPLIDE_SELECTOR = '.splide-carousel-block-search-camping'

  function getOptionsFrom(el) {
    try {
      return Object.assign(
        {
          perPage: 4,
          gap: '1rem',
          pagination: false,
          arrows: true,
          perMove: 1,
          breakpoints: {
            1024: { perPage: 2 },
            640: {
              perPage: 3,
              direction: 'ttb',
              height: '1400px',
              wheel: false,
              fixedHeight: '450px',
              drag: false,
              pagination: true,
            },
          },
        },
        JSON.parse(el.getAttribute('data-splide') || '{}'),
      )
    } catch (e) {
      return { perPage: 4, gap: '1rem' }
    }
  }

  function mountSplides(root = document) {
    root.querySelectorAll(SPLIDE_SELECTOR).forEach((el) => {
      if (el[SPLIDE_KEY]) {
        try {
          el[SPLIDE_KEY].refresh()
        } catch (_) {}
        return
      }
      const opts = getOptionsFrom(el)
      const inst = new Splide(el, opts)
      inst.mount()
      el[SPLIDE_KEY] = inst
    })
  }

  function destroySplides(root = document) {
    root.querySelectorAll(SPLIDE_SELECTOR).forEach((el) => {
      const inst = el[SPLIDE_KEY]
      if (inst) {
        try {
          inst.off && inst.off('*')
        } catch (_) {}
        inst.destroy(true)
        el[SPLIDE_KEY] = null
      }
    })
  }

  document.addEventListener('facetwp-refresh', () => {
    destroySplides(document)
  })

  document.addEventListener('facetwp-loaded', () => {
    const wrapper =
      document.querySelector('#facetwp-template, .facetwp-template') || document

    if (window.imagesLoaded) {
      imagesLoaded(wrapper, () => mountSplides(wrapper))
    } else {
      mountSplides(wrapper)
    }
  })

  document.addEventListener('DOMContentLoaded', () => {
    mountSplides(document)
  })
}

const activeFilters = document.querySelector('.active-filters')
const facetwpFacets = document.querySelectorAll(
  '.block-search-campings .facetwp-facet',
)

if (activeFilters) {
  activeFilters.addEventListener('click', function () {
    facetwpFacets.forEach((facet) => {
      facet.style.display = facet.style.display === 'block' ? 'none' : 'block'
    })

    const toggleIcon = activeFilters.querySelector('span:last-child')
    if (toggleIcon) {
      toggleIcon.textContent = toggleIcon.textContent.trim() === '+' ? '-' : '+'
    }
  })
}

document.addEventListener('DOMContentLoaded', () => {
  const activeFiltersBlockCampings = document.querySelector(
    '.active-filters-block-campings',
  )
  const facetwpFacetsBlockCampings = document.querySelectorAll(
    '.block-campings .ctitle',
  )
  const blocks = document.querySelectorAll('.block-campings .facet-block')

  if (!activeFiltersBlockCampings) return

  activeFiltersBlockCampings.addEventListener('click', () => {

    facetwpFacetsBlockCampings.forEach((facet) => {
      // On toggle manuellement la visibilité mobile
      if (facet.classList.contains('max-md:hidden')) {
        facet.classList.remove('max-md:hidden')
        facet.classList.add('max-md:block') // ou max-md:block selon ton besoin
        facet.style.marginTop = '20px'
      } else {
        facet.classList.remove('max-md:block', 'max-md:block')
        facet.classList.add('max-md:hidden')
        facet.style.marginTop = '0px'
        blocks.forEach((block) => {
           const wrapper = block.querySelector('.facet-wrapper')   
           wrapper.classList.remove('max-md:block')
           wrapper.classList.add('max-md:hidden')
        })
      }
    })

    const toggleIcon =
      activeFiltersBlockCampings.querySelector('span:last-child')
    if (toggleIcon) {
      toggleIcon.textContent = toggleIcon.textContent.trim() === '+' ? '-' : '+'
    }
  })
})

document.addEventListener('DOMContentLoaded', () => {
  const blocks = document.querySelectorAll('.facet-block')

  blocks.forEach((block) => {
    const title = block.querySelector('.ctitle')
    const wrapper = block.querySelector('.facet-wrapper')
    if (!title || !wrapper) return

    // Améliore l’accessibilité + UX
    title.setAttribute('role', 'button')
    title.setAttribute('tabindex', '0')
    title.classList.add('cursor-pointer')

    const toggle = () => {
      // On ne touche qu’au comportement mobile (<= md)
      if (wrapper.classList.contains('max-md:hidden')) {
        wrapper.classList.remove('max-md:hidden')
        wrapper.classList.add('max-md:block') // ou 'max-md:flex' si tu préfères
        title.setAttribute('aria-expanded', 'true')
      } else {
        wrapper.classList.remove('max-md:block', 'max-md:flex')
        wrapper.classList.add('max-md:hidden')
        title.setAttribute('aria-expanded', 'false')
      }
    }

    title.addEventListener('click', toggle)
    title.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault()
        toggle()
      }
    })
  })
})

//splide-carousel-block-search-camping

export default function blockSearch() {
  // === Splide x FacetWP ===
  const SPLIDE_KEY = '_splideInstance'
  const SPLIDE_SELECTOR = '.splide-carousel-block-search-camping'

  function getOptionsFrom(el) {
    // Option 1: via data-splide='{"perPage":3,"gap":"1rem"}'
    try {
      return Object.assign(
        {
          type: 'loop',
          perPage: 4,
          gap: '1rem',
          pagination: true,
          arrows: true,
          // Responsive (modifie si besoin)
          breakpoints: {
            1024: { perPage: 2 },
            640: { perPage: 1 },
          },
        },
        JSON.parse(el.getAttribute('data-splide') || '{}'),
      )
    } catch (e) {
      return { type: 'loop', perPage: 4, gap: '1rem' }
    }
  }

  function mountSplides(root = document) {
    root.querySelectorAll(SPLIDE_SELECTOR).forEach((el) => {
      // Évite double init
      if (el[SPLIDE_KEY]) {
        try {
          el[SPLIDE_KEY].refresh()
        } catch (_) {}
        return
      }
      const opts = getOptionsFrom(el)
      const inst = new Splide(el, opts)
      inst.mount() // si extensions: inst.mount({ AutoScroll })
      el[SPLIDE_KEY] = inst
    })
  }

  function destroySplides(root = document) {
    root.querySelectorAll(SPLIDE_SELECTOR).forEach((el) => {
      const inst = el[SPLIDE_KEY]
      if (inst) {
        // Detach listeners avant destroy (parfois utile avec extensions)
        try {
          inst.off && inst.off('*')
        } catch (_) {}
        inst.destroy(true) // true => nettoie le DOM réinjecté par Splide
        el[SPLIDE_KEY] = null
      }
    })
  }

  // 1) FacetWP va rafraîchir -> détruire les carousels existants
  document.addEventListener('facetwp-refresh', () => {
    destroySplides(document)
  })

  // 2) FacetWP a injecté les nouveaux résultats -> remonter les carousels
  document.addEventListener('facetwp-loaded', () => {
    // Si les slides contiennent des images sans dimensions, on peut attendre leur charge
    const wrapper =
      document.querySelector('#facetwp-template, .facetwp-template') || document

    if (window.imagesLoaded) {
      imagesLoaded(wrapper, () => mountSplides(wrapper))
    } else {
      mountSplides(wrapper)
    }
  })

  // 3) Premier chargement
  document.addEventListener('DOMContentLoaded', () => {
    mountSplides(document)
  })
}

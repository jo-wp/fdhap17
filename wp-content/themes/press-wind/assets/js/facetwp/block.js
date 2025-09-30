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
            640: { perPage: 3, direction: 'ttb', height: '1320px', wheel: true,fixedHeight:'450px',drag:false,pagination:true },
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


const activeFilters = document.querySelector('.active-filters');
const facetwpFacets = document.querySelectorAll('.block-search-campings .facetwp-facet');

if (activeFilters) {
  activeFilters.addEventListener('click', function() {
    facetwpFacets.forEach(facet => {
      facet.style.display = (facet.style.display === "block") ? "none" : "block";
    });

    const toggleIcon = activeFilters.querySelector('span:last-child');
    if (toggleIcon) {
      toggleIcon.textContent = (toggleIcon.textContent.trim() === "+") ? "-" : "+";
    }
  });
}

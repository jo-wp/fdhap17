import importObserver from './importObserver.js'
import displayCardMapCamping from './campings/map.js'
import generateSummary from './articles/main.js'
import instagramOverride from './instagram/main.js'
import filtreMobile from './facetwp/mobile.js'
import menuMobile from './menu/main.js'
import ctaMobile from './menu/cta.js'
import displayCardMapSearchCampings from './campings/block-search-campings.js'
import { ensureMap, rebuildMarkers, initFacetwpLeaflet } from './facetwp/map.js'
import blockSearch from './facetwp/block.js'
import isSticky from './menu/sticky.js'
import Splide from '@splidejs/splide'
import initFDHPAModal from './campings/modal.js'


window.Splide = Splide

function menuToggle() {
  const menuLinks = document.querySelectorAll('.submenu li')
  menuLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      const underMenu = link.querySelector('.submenu-child')
      // e.preventDefault()

      //disable all active
      menuLinks.forEach((link) => {
        const underMenu = link.querySelector('.submenu-child')
        if (underMenu) {
          underMenu.classList.remove('active')
        }
        link.classList.remove('active')
      })

      if (underMenu) {
        underMenu.classList.toggle('active')
      }

      link.classList.toggle('active')
    })
  })
}

function menuMobileToggle() {
  const openBtn = document.querySelector('.open-menu-mobile')
  const closeBtn = document.querySelector('.close-menu-mobile')
  const navigationWrapper = document.querySelector(
    '.block-hero__content__navigation',
  )
  const navigation = document.querySelector(
    '.block-hero__content__navigation > div',
  )
  const navigationMiniSite = document.querySelector('.block-minisite__menu')
  const targetNav = navigation || navigationMiniSite

  let scrollY = 0 // pour mémoriser la position avant blocage

  if (openBtn && closeBtn && targetNav) {
    // Ouvrir le menu
    openBtn.addEventListener('click', function (e) {
      e.preventDefault()

      if (navigationWrapper) {
        navigationWrapper.style.zIndex = '9999'
      }

      targetNav.classList.add('active')
      openBtn.classList.add('hidden')
      closeBtn.classList.remove('hidden')

      // 🔒 Bloquer le scroll (mobile + desktop)
      scrollY = window.scrollY
      document.body.style.position = 'fixed'
      document.body.style.top = `-${scrollY}px`
      document.body.style.left = '0'
      document.body.style.right = '0'
      document.body.style.width = '100%'
    })

    // Fermer le menu
    closeBtn.addEventListener('click', function (e) {
      e.preventDefault()
      if (navigationWrapper) {
        navigationWrapper.style.zIndex = '2'
      }

      targetNav.classList.remove('active')
      closeBtn.classList.add('hidden')
      openBtn.classList.remove('hidden')

      // 🔓 Réactiver le scroll
      document.body.style.position = ''
      document.body.style.top = ''
      document.body.style.left = ''
      document.body.style.right = ''
      document.body.style.width = ''
      window.scrollTo(0, scrollY) // revenir à la position précédente
    })
  }
}

function revealPhone() {
  const phones = document.querySelectorAll('.reveal-phone')
  if (!phones || phones.length === 0) return
  phones.forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!el.classList.contains('revealed')) {
        e.preventDefault() 
        el.textContent = el.dataset.full
        el.classList.add('revealed')
      }
    })
  })
}

function splideJsBlockIdea() {
  const carousel = document.querySelector('.splide__carousel__block_idea')
  const block_idea__filters_controls_next = document.querySelector(
    '.block-idea__filters-controls-next',
  )
  const block_idea__filters_controls_prev = document.querySelector(
    '.block-idea__filters-controls-prev',
  )

  if (carousel) {
    const carouselSplide = new Splide(carousel, {
      perPage: 4,
      perMove: 1,
      gap: '40px',
      pagination: false,
      arrows: false,
      heightRatio: 0.3,
      breakpoints: {
        768: {
          destroy: true,
        },
      },
    })

    block_idea__filters_controls_next.addEventListener('click', () => {
      carouselSplide.go('>')
    })

    block_idea__filters_controls_prev.addEventListener('click', () => {
      carouselSplide.go('<')
    })

    carouselSplide.mount()

    const slides = [...document.querySelectorAll('.splide__slide-item')]

    const handleFilter = (taxonomie) => {
      slides.forEach((slide) => slide.classList.remove('splide__slide'))

      slides
        .filter((slide) => slide.dataset.taxonomie === taxonomie)
        .forEach((slide) => slide.classList.add('splide__slide'))

      carouselSplide.refresh()
    }

    const initFilter = () => {
      const filterButtons = document.querySelectorAll('.filters__button')

      filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
          filterButtons.forEach((btn) => btn.classList.remove('active'))
          button.classList.add('active')
          handleFilter(button.dataset.filter)
        })
      })

      // 👉 Activer automatiquement le 1er filtre en mobile
      if (window.innerWidth < 768 && filterButtons.length > 0) {
        const firstButton = filterButtons[0]
        firstButton.classList.add('active')
        handleFilter(firstButton.dataset.filter)
      }
    }

    initFilter()
  }
}

// function expandFAQItem() {
//   const questions = document.querySelectorAll('.block-faq li h3')

//   questions.forEach((question) => {
//     question.addEventListener('click', () => {
//       question.parentElement.classList.toggle('active')
//       question.parentElement.querySelector('p').classList.toggle('invisible')
//       question.parentElement.querySelector('p').classList.toggle('h-0')
//     })
//   })
//   // questions.forEach(question => {
//   //   question.classList.toggle('invisible')
//   // })
// }
function carouselDescription() {
  const carouselList = document.querySelector('.carousel__list')
  const carouselItems = Array.from(document.querySelectorAll('.carousel__item'))
  const dotsContainer = document.querySelector('.carousel__dots')
  const dots = dotsContainer
    ? Array.from(dotsContainer.querySelectorAll('.carousel__dot'))
    : []

  if (!carouselList || !carouselItems.length || !dotsContainer) return

  // --- Assigner un index aux slides si absent (ordre DOM)
  carouselItems.forEach((item, i) => {
    if (!item.dataset.index) item.dataset.index = String(i)
  })

  // --- Sécurité : ne pas dépasser le nb de slides
  const nb = Math.min(dots.length, carouselItems.length)

  // --- Init état des dots selon le slide actif (data-pos == 0)
  syncDots()

  // Clic sur un slide
  carouselList.addEventListener('click', (event) => {
    const newActive = event.target.closest('.carousel__item')
    if (!newActive || newActive.classList.contains('carousel__item_active'))
      return
    update(newActive)
  })

  // Clic sur un dot
  dotsContainer.addEventListener('click', (event) => {
    const dot = event.target.closest('.carousel__dot')
    if (!dot) return
    const index = dot.dataset.index
    const slide = carouselItems.find((el) => el.dataset.index === index)
    if (!slide || slide.classList.contains('carousel__item_active')) return
    update(slide)
  })

  // --- Update positions + états
  function update(newActive) {
    const newActivePos = newActive.dataset.pos

    const current = carouselItems.find((el) => el.dataset.pos == 0)
    const prev = carouselItems.find((el) => el.dataset.pos == -1)
    const next = carouselItems.find((el) => el.dataset.pos == 1)
    const first = carouselItems.find((el) => el.dataset.pos == -2)
    const last = carouselItems.find((el) => el.dataset.pos == 2)

    if (current) current.classList.remove('carousel__item_active')
    newActive.classList.add('carousel__item_active')
    ;[current, prev, next, first, last].forEach((item) => {
      if (!item) return
      const itemPos = item.dataset.pos
      item.dataset.pos = getPos(itemPos, newActivePos)
    })

    syncDots()
  }

  // --- Met à jour la classe .active des dots selon le slide au centre (pos 0)
  function syncDots() {
    const active = carouselItems.find((el) => el.dataset.pos == 0)
    const activeIdx = active ? active.dataset.index : null
    for (let i = 0; i < nb; i++) {
      const dot = dots[i]
      const isActive = dot.dataset.index === activeIdx
      dot.classList.toggle('active', isActive)
    }
  }

  function getPos(current, active) {
    const diff = current - active
    if (Math.abs(diff) > 2) return -current
    return diff
  }
}

function authorQuoteSlider() {
  const carousel = document.querySelector('.splide_author-quote')
  if (!carousel) return
  const carouselSplide = new Splide(carousel, {
    type: 'loop',
    perPage: 1,
    perMove: 1,
    gap: '40px',
    arrows: false,
    pagination: true,
    // heightRatio: 0.5,
    autoplay: true,
    interval: 5000,
  })
  carouselSplide.mount()
}

function animationBlock() {
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.2,
  }

  function observerCallback(entries, observer) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.replace('fadeOutAnimation', 'fadeInAnimation')
      }
      // else {
      // 	entry.target.classList.replace('fadeIn', 'fadeOut');
      // }
    })
  }
  const observer = new IntersectionObserver(observerCallback, observerOptions)
  const fadeElms = document.querySelectorAll('.animateFade')
  fadeElms.forEach((el) => observer.observe(el))
}

;(function () {
  const searchBar = document.querySelector('.search-bar')

  function isSearchBarInBottomHalf() {
    if (!searchBar) return false
    const r = searchBar.getBoundingClientRect()
    const middleY = r.top + r.height / 2
    return middleY > window.innerHeight / 2
  }

  function applyAboveBelow() {
    const cal = document.querySelector('.fdate-wrap.opened') // calendrier visible
    if (!searchBar || !cal) return

    const bottomHalf = isSearchBarInBottomHalf()

    // toggle sur la search-bar
    searchBar.classList.toggle('above', bottomHalf)

    // toggle sur le calendrier
    cal.classList.toggle('above', bottomHalf)
    cal.classList.toggle('below', !bottomHalf)
  }

  // écoute les changements
  window.addEventListener('scroll', applyAboveBelow, { passive: true })
  window.addEventListener('resize', applyAboveBelow)

  // détecte ouverture du calendrier
  const mo = new MutationObserver(() => applyAboveBelow())
  mo.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class'],
  })

  // init
  applyAboveBelow()
})()

// carouselDescription()

document.addEventListener('DOMContentLoaded', () => {
  splideJsBlockIdea()

  // expandFAQItem()
  carouselDescription()
  menuToggle()
  menuMobileToggle()
  authorQuoteSlider()
  generateSummary()
  ensureMap()
  rebuildMarkers()
  initFacetwpLeaflet()
  animationBlock()
  blockSearch()
  filtreMobile()
  instagramOverride()
  menuMobile()
  ctaMobile()
  isSticky()
  revealPhone()
  initFDHPAModal()
  //Import
  const map = document.querySelector('#map')
  if (map) {
    displayCardMapCamping()
  }

  // importObserver
  // use only name of file without extension and ./, root is ./assets/js
  importObserver(document.querySelector('.site-footer'), 'hello')
})

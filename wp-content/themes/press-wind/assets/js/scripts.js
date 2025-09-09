import importObserver from './importObserver.js'
import displayCardMapCamping from './campings/map.js'
import generateSummary from './articles/main.js'
import instagramOverride from './instagram/main.js';
import Splide from '@splidejs/splide'
window.Splide = Splide

function menuToggle() {
  const menuLinks = document.querySelectorAll('.submenu li')
  menuLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      const underMenu = link.querySelector('.submenu-child')
      e.preventDefault()

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

function menuMobileToggle(){
   const openBtn = document.querySelector(".open-menu-mobile");
  const closeBtn = document.querySelector(".close-menu-mobile");
  const navigation = document.querySelector(".block-hero__content__navigation");

  if (openBtn && closeBtn && navigation) {
    // Ouvrir le menu
    openBtn.addEventListener("click", function (e) {
      e.preventDefault();
      navigation.classList.add("active");
      openBtn.classList.add("hidden");
      closeBtn.classList.remove("hidden");
    });

    // Fermer le menu
    closeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      navigation.classList.remove("active");
      closeBtn.classList.add("hidden");
      openBtn.classList.remove("hidden");
    });
  }
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
      // type       : 'fade',
      // heightRatio: 0.5,
      // type: 'loop',
      perPage: 4,
      perMove: 1,
      gap: '40px',
      pagination: false,
      arrows: false,
      heightRatio: 0.3,
      breakpoints:{
        768:{
          destroy:true
        }
      }
    })
    block_idea__filters_controls_next.addEventListener('click', () => {
      carouselSplide.go('>')
    })

    block_idea__filters_controls_prev.addEventListener('click', () => {
      carouselSplide.go('<')
    })
    carouselSplide.mount()
  }
}

function expandFAQItem() {
  const questions = document.querySelectorAll('.block-faq li h3')

  questions.forEach((question) => {
    question.addEventListener('click', () => {
      question.parentElement.classList.toggle('active')
      question.parentElement.querySelector('p').classList.toggle('invisible')
      question.parentElement.querySelector('p').classList.toggle('h-0')
    })
  })
  // questions.forEach(question => {
  //   question.classList.toggle('invisible')
  // })
}

function carouselDescription() {
  const state = {}
  const carouselList = document.querySelector('.carousel__list')
  const carouselItems = document.querySelectorAll('.carousel__item')
  const elems = Array.from(carouselItems)

  if (carouselList === null) return

  carouselList.addEventListener('click', function (event) {
    var newActive = event.target
    var isItem = newActive.closest('.carousel__item')

    if (!isItem || newActive.classList.contains('carousel__item_active')) {
      return
    }

    update(newActive)
  })

  const update = function (newActive) {
    const newActivePos = newActive.dataset.pos

    const current = elems.find((elem) => elem.dataset.pos == 0)
    const prev = elems.find((elem) => elem.dataset.pos == -1)
    const next = elems.find((elem) => elem.dataset.pos == 1)
    const first = elems.find((elem) => elem.dataset.pos == -2)
    const last = elems.find((elem) => elem.dataset.pos == 2)

    current.classList.remove('carousel__item_active')
    ;[current, prev, next, first, last].forEach((item) => {
      var itemPos = item.dataset.pos

      item.dataset.pos = getPos(itemPos, newActivePos)
    })
  }

  const getPos = function (current, active) {
    const diff = current - active

    if (Math.abs(current - active) > 2) {
      return -current
    }

    return diff
  }
}

document.addEventListener('DOMContentLoaded', () => {
  splideJsBlockIdea()
  expandFAQItem()
  carouselDescription()
  menuToggle()
  menuMobileToggle()
  instagramOverride()

  generateSummary()

  //Import
  const map = document.querySelector('#map')
  if (map) {
    displayCardMapCamping()
  }

  // importObserver
  // use only name of file without extension and ./, root is ./assets/js
  importObserver(document.querySelector('.site-footer'), 'hello')
})

// we recommend preflight to false for avoid conflict in editor
const usePreflightFront = false

module.exports = {
  // use preflight (reset CSS) override fonts size from theme.json
  corePlugins: {
    preflight: process.env.IS_EDITOR ? false : usePreflightFront,
  },
  content: [
    './templates/**/*.php',
    './blocks/**/*.php',
    './partials/**/**/*.php',
    './partials/**/*.php',
    './inc/**/*.php',
     '../../plugins/camping-favorites/**/*.php',
    '*.php',
    './assets/*.{js,jsx,ts,tsx,vue}',
  ],
  safelist: [],
  theme: {
    fontFamily: {
      display: ['PlayfairDisplay'],
      jakarta: ['Plus Jakarta Sans', 'sans-serif'],
      montserrat: ['Montserrat'],
      body: ['Arial', 'Helvetica', 'sans-serif'],
      ivymode: ['IvyMode'],
      arial: ['Arial', 'Helvetica', 'sans-serif'],
      marcellus :['Marcellus', 'serif']
    },
    extend: {
      gridTemplateColumns: {
        main: '8rem 1fr 8rem',
        'main-small': '1rem 1fr 1rem',
      },
      maxWidth: {
        'huge': '1890px',
        'screen-xl': '1860px',
      },
      opacity: {
        '37': '.37',
      },
      colors: {
        black: '#333333',
        green:'#51AB7E',
        orange: '#E9B237',
        orangeGlow: '#FB920F',
        primary: '#51AB7E',
        bgOrange : '#FFFAEE',
        bgGreen : '#ECF5F0'
      },
      backgroundImage: (theme) => ({
        'wp-performance': "url('/assets/media/wp-performance.png')",
        'more-icon': "url('/assets/media/more.svg')",
        'arrow-menu': "url('/assets/media/arrow-menu.svg')",
        'arrow-menu-orange': "url('/assets/media/arrow-menu-orange.svg')",
        'arrow-menu-green': "url('/assets/media/arrow-menu-green.svg')",
        'arrow-mini-green': "url('/assets/media/arrow-mini-green.svg')",
        'arrow-menu-black': "url('/assets/media/arrow-menu-black.svg')",
        'check': "url('/assets/media/check.svg')",
        'arrow-list' : "url('/assets/media/arrow-list.svg')",
        'arrow-menu-mobile' : "url('/assets/media/arrow-menu-mobile.svg')",
        'checkbox' : "url('/assets/media/checkbox-filter.png')",
        'checkbox-checked' : "url('/assets/media/checkbox-filter-checked.png')",
        'zoom' : "url('/assets/media/loupe.svg')",
        'bg-carousel-block-camping':"url('/assets/media/button-carousel-block.svg')",
        'arrow-black' : "url('/assets/media/arrow_black.svg')",
        'arrow-button' : "url('/assets/media/arrow-button.svg')"
      }),
    },
  },
  plugins: [require('@_tw/themejson')(require('./theme.json'))],
}

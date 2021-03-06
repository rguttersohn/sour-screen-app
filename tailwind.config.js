module.exports = {
  purge: [],
  darkMode: false, // or 'media' or 'class'
  theme: {
   extend:{
    fontFamily: {
      mono: ['VT323', 'mono'],
    },
    colors:{
      red:{
        main:'#ff3333',
        light:"#ff9999",
        xLight:"#ffcccc"
      },
      blue:{
        main:"#0099cc",
        light:"#99ccff",
        xLight:"#ccccff"
      }
    }
   }
  },
  variants: {
    extend: {},
  },
  plugins: [],
}

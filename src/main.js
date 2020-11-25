import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')

fetch('http://localhost:8888/wp-json/wp/v2/posts')
  .then(resp=>resp.json()).then(posts=>console.log(posts))

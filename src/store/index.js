import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://localhost:8888/wp-json/wp/v2",
    baseHostURL:"http://localhost:8888"
  },
  mutations: {
  },
  actions: {
  },
  modules: {
  }
})

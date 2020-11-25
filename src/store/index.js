import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://localhost:8888/wp-json/wp/v2",
    baseHostURL:"http://localhost:8888",
    posts:"",
    count:0
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts`)
      .then(resp=>resp.json())
      .then(posts=>
        state.posts = posts
        )
    },
    INCREMENT(state){
      state.count++
    }
  },
  actions: {
    getPosts(context){
      context.commit('GET_POSTS')
    },
    increment(context){
      context.commit('INCREMENT')
    }
  },
  modules: {
  }
})

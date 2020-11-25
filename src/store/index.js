import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://localhost:8888/wp-json/wp/v2",
    baseHostURL:"http://localhost:8888",
    posts:"",
    movies:"",
    lists:""
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts`)
      .then(resp=>resp.json())
      .then(posts=>{
        state.posts = posts
        state.lists = posts.filter(post=>post.categories[0] === 3)
        state.movies =posts.filter(post=>post.categories[0] === 4)
      })
    },
  },
  actions: {
    getPosts(context){
      context.commit('GET_POSTS')
    },
  },
  modules: {
  }
})

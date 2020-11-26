import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://localhost:8888/wp-json/wp/v2",
    baseHostURL:"http://localhost:8888",
    posts:[],
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts/?_embed`)
      .then(resp=>resp.json())
      .then(posts=>{
        state.posts = posts
      })
    },
  },
  actions: {
    getPosts(context){
      context.commit('GET_POSTS')
    },
  },
  modules: {
  },
  getters:{
    movies(state){
      return state.posts.filter(post=>post.categories[0]===4)
    },
    lists(state){
      return state.posts.filter(post=>post.categories[0] === 3)
    },
    newMovies(state, getters){
      return getters.movies.slice(0,3)
    },
    newLists(state,getters){
      return getters.lists.slice(0,3)
    }
  }
})

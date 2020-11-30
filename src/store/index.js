import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "https://www.api-sourscreen.com/wp-json/wp/v2",
    baseHostURL:"https://www.api-sourscreen.com/",
    posts:[],
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts/?_embed&per_page=99`)
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
      return state.posts.filter(post=>post.categories[post.categories.findIndex(cat=>cat === 2)] === 2)
    },
    lists(state){
      return state.posts.filter(post=>post.categories[post.categories.findIndex(cat=>cat === 3)] === 3)
    },
    newMovies(state, getters){
      return getters.movies.slice(0,3)
    },
    newLists(state,getters){
      return getters.lists.slice(0,3)
    }
  }
})

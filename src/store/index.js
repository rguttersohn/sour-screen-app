import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://3.89.20.61/wp-json/wp/v2",
    baseHostURL:"http://3.89.20.61/",
    posts:[],
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts/?_embed`)
      .then(resp=>resp.json())
      .then(posts=>{
        console.log(posts)
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
      return state.posts.filter(post=>post.categories[1] === 2)
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

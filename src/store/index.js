import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "https://www.api-sourscreen.com/wp-json/wp/v2",
    baseHostURL:"https://www.api-sourscreen.com/",
    posts:[],
    iconHover:false,
    iconTooltipX:0,
    iconTooltipY:0
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts/?_embed&per_page=99`)
      .then(resp=>resp.json())
      .then(posts=>{
        state.posts = posts
      })
    },
    ICON_HOVER_TRUE(state){
      state.iconHover = true
    },
    ICON_HOVER_FALSE(state){
      state.iconHover = false
    },
    SET_ICON_TOOLTIP_COORDS(state,{x,y}){
      state.iconTooltipX = x
      state.iconTooltipY = y
    }
  },
  actions: {
    getPosts(context){
      context.commit('GET_POSTS')
    }
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

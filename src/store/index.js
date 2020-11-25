import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    baseAPIURL: "http://localhost:8888/wp-json/wp/v2",
    baseHostURL:"http://localhost:8888",
    posts:"",
    lists:"",
    movies:""
  },
  mutations: {
    GET_POSTS(state){
      fetch(`${state.baseAPIURL}/posts`)
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
  getters:{
    lists:state=>state.posts.filter(post=>post.categories[0]===3),
    movies:state=>state.posts.filter(post=>post.categories[0]===4)
  },
  modules: {
  }
})

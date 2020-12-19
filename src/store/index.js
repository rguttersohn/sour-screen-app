import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    baseAPIURL: "https://www.api-sourscreen.com/wp-json/wp/v2",
    baseHostURL: "https://www.api-sourscreen.com/",
    posts: [],
    relatedPosts: [],
    currentPost: "",
    newUserMessage:{}
  },
  mutations: {
    GET_POSTS(state) {
      fetch(`${state.baseAPIURL}/posts/?_embed&per_page=99`)
        .then((resp) => resp.json())
        .then((posts) => {
          state.posts = posts;
        });
    },
    CREATE_USER(state){
      fetch(`${state.baseAPIURL}/users/register`,{
        method:'POST',
        body:JSON.stringify({username:'Louis',email:'louis@gmail.com',password:'dogs123'}),
        headers:{
         'Content-Type':'application/json;charset=UTF-8'
        }
      }).then(resp=>resp.json())
      .then(json=>state.newUserMessage = json.message)
      .catch(error=>console.error(error))
    },
    GET_CURRENT_POST(state, id) {
      fetch(`${state.baseAPIURL}/posts/${id}?_embed`)
        .then((resp) => resp.json())
        .then((post) => {
          state.currentPost = post;
        });
    },
    PUSH_TO_RELATED(state) {
      let postsCopy = state.posts.slice()
      postsCopy.forEach((post) => {
        post.relatedScore = 0;
        if (post.id !== state.currentPost.id) {
          post._embedded["wp:term"][0].forEach((el) => {
            for (
              let i = 0;
              i < state.currentPost._embedded["wp:term"][0].length;
              i++
            ) {
              if (
                el.name === state.currentPost._embedded["wp:term"][0][i].name
              ) {
                post.relatedScore = post.relatedScore + 3;
              }
            }
          });
          post._embedded["wp:term"][1].forEach((el) => {
            for (
              let i = 0;
              i < state.currentPost._embedded["wp:term"][1].length;
              i++
            ) {
              if (
                el.name === state.currentPost._embedded["wp:term"][1][i].name
              ) {
                post.relatedScore = post.relatedScore + 1;
              }
            }
          });
        }
      });
      state.relatedPosts = postsCopy
        .sort((a, b) => a.relatedScore - b.relatedScore)
        .reverse()
        .slice(0, 5);
    },
  },
  actions: {
    getPosts(context) {
      context.commit("GET_POSTS");
    },
    pushToRelated(context) {
      context.commit("PUSH_TO_RELATED");
    },
    getCurrentPost(context, id) {
      context.commit("GET_CURRENT_POST", id);
    },
  },
  modules: {},
  getters: {
    movies(state) {
      return state.posts.filter(
        (post) =>
          post.categories[post.categories.findIndex((cat) => cat === 2)] === 2
      );
    },
    lists(state) {
      return state.posts.filter(
        (post) =>
          post.categories[post.categories.findIndex((cat) => cat === 3)] === 3
      );
    },
    newMovies(state, getters) {
      return getters.movies.slice(0, 3);
    },
    newLists(state, getters) {
      return getters.lists.slice(0, 3);
    },
    action(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 7)] === 7
      );
    },
    christmas(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 19)] === 19
      );
    },
    christian(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 6)] === 6
      );
    },
    horror(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 48)] === 48
      );
    },
    disneyChannel(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 55)] === 55
      );
    },
    drama(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 5)] === 5
      );
    },
  },
});

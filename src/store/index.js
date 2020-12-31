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
    serverMessage: "",
    serverCode: "",
    username: "",
    accessToken: "",
    userInfo:{
      id:"",
      username:""
    }
  },
  mutations: {
    GET_POSTS(state) {
      fetch(`${state.baseAPIURL}/posts/?_embed&per_page=99`)
        .then((resp) => resp.json())
        .then((posts) => {
          state.posts = posts;
        });
    },
    GET_CURRENT_POST(state, id) {
      fetch(`${state.baseAPIURL}/posts/${id}?_embed`)
        .then((resp) => resp.json())
        .then((post) => {
          state.currentPost = post;
        });
    },
    PUSH_TO_RELATED(state) {
      let postsCopy = state.posts.slice();
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
    SUBMIT_NEW_ACCOUNT(state, accountInfo) {
      fetch(`${state.baseAPIURL}/users/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(accountInfo),
      })
        .then((resp) => resp.json())
        .then((data) => {
          state.serverMessage = data.message;
          state.serverCode = data.code;
        });
    },
    SUBMIT_LOGIN(state, loginInfo) {
      fetch(`${state.baseHostURL}wp-json/jwt-auth/v1/token/`, {
        method: "POST",
        body: JSON.stringify(loginInfo),
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((resp) => resp.json())
        .then((user) => {
          if (user.code) {
            console.log(user.code);
          } else {
            state.accessToken = user.token
            window.localStorage.accessToken = user.token
            window.localStorage.username = user.user_display_name
            window.localStorage.id = user.user_id
            state.userInfo.id = user.user_id
            state.userInfo.username = user.user_display_name
            
          }
        });
    },
    GET_USER_INFO(state) {
      if (window.localStorage.accessToken){
      state.accessToken = window.localStorage.accessToken
      state.userInfo.id = window.localStorage.id
      state.userInfo.username = window.localStorage.username
      }
    },
    REMOVE_USER_INFO(state){
      if(window.localStorage.accessToken){
        state.accessToken = "";
        state.userInfo.id = "";
        state.userInfo.username = "";
        window.localStorage.removeItem('id')
        window.localStorage.removeItem('username')
        window.localStorage.removeItem('accessToken')
      }
    }
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
    submitNewAccount(context, accountInfo, formMessage) {
      context.commit("SUBMIT_NEW_ACCOUNT", accountInfo, formMessage);
    },
    submitLogin(context, loginInfo) {
      context.commit("SUBMIT_LOGIN", loginInfo);
    }
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
          movie.categories[movie.categories.findIndex((cat) => cat === 19)] ===
          19
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
          movie.categories[movie.categories.findIndex((cat) => cat === 48)] ===
          48
      );
    },
    disneyChannel(state, getters) {
      return getters.movies.filter(
        (movie) =>
          movie.categories[movie.categories.findIndex((cat) => cat === 55)] ===
          55
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

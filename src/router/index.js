import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import List from "../views/List.vue";
import Database from "../views/Database.vue";
import Post from "../views/Post.vue";
import CreateAccount from "../views/CreateAccount.vue";
import User from "../views/User.vue"

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/lists",
    name: "Lists",
    component: List,
  },
  { path: "/movies", name: "Movies", component: Database },
  { path: "/post/:id", name: "Post", component: Post },
  {path:"/createaccount",name:"CreateAccount", component:CreateAccount},
  {path:"/user:id",name:"User", component:User}
];

const router = new VueRouter({
  routes,
  mode: "history",
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { x: 0, y: 0 };
    }
  },
});

export default router;

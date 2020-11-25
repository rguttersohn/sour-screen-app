import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import List from '../views/List.vue';
import Database from '../views/Database.vue';
import Post from '../views/Post.vue'


Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/list",
    name: "List",
    component: List,
  },
  { path: "/database", name: "Database", component: Database },
  {path:"/post",name:"Post",component:Post}
];

const router = new VueRouter({
  routes,
});

export default router;

<template>
  <div>
    <h2>Our lists:</h2>
    <div v-for="post in posts" :key="post.id">
      <router-link :to="{name:'Post',params:{id:post.id}}" >
            <h3 v-if="post.categories[0] === 3">{{post.title.rendered}}</h3>
            <p  v-if="post.categories[0] === 3" v-html="post.excerpt.rendered"></p>
      </router-link>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  computed: mapState({
    posts: (state) => state.posts,
  }),
  created: function () {
    this.$store.dispatch("getPosts");
  },
};
</script>

<style lang="sass" scoped>
</style>

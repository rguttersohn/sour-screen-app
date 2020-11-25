<template>
  <div v-if="post !== ''">
    <h2>{{ post.title.rendered }}</h2>
    <img :src="image" />
    <div v-html="post.content.rendered"></div>
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  data() {
    return {
      post: "",
      id: this.$route.params.id,
      image: "",
    };
  },
  computed: mapState({
    baseAPIURL: (state) => state.baseAPIURL,
    baseURL: (state) => state.baseURL,
  }),
  created() {
    fetch(`${this.baseAPIURL}/posts/${this.id}?_embed`)
      .then((resp) => resp.json())
      .then((post) => {
        if (post._embedded["wp:featuredmedia"]) {
          this.image = post._embedded["wp:featuredmedia"][0].source_url;
        }
        this.post = post;
      });
  },
};
</script>

<style scoped>
h2 {
  font-weight: 500;
}
</style>
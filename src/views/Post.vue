<template>
  <div class="post-container">
    <div v-if="post !== ''">
      <img :src="image" />
      <h1>{{ post.title.rendered }}</h1>
      <div v-html="post.excerpt.rendered" class="post-excerpt-container"></div>
      <div v-html="post.content.rendered" class="post-content-container"></div>
    </div>
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

<style lang="scss">
.post-container {
  width: 100vw;

  img {
    width: 100vw;
    height: 400px;
    object-fit: cover;
    object-position: 0 5%;
  }

  .post-excerpt-container p {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 18px;
    font-style: normal;
    font-variant: normal;
    font-weight: 700;
    line-height: 15.4px;
  }

  .post-content-container {
    width: 75%;
    margin: auto;
  }
}
</style>
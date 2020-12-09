<template>
  <div class="w-screen lg:py-36 py-24">
    <section class="w-full" id="intro-section" v-if="currentPost !== ''">
      <div class="w-9/12 m-auto">
        <h1
          v-html="currentPost.title.rendered"
          class="text-red-light mb-6"
        ></h1>
        <div class="mb-6 excerpt" v-html="currentPost.excerpt.rendered"></div>
      </div>
    </section>

    <div class="grid grid-cols-6 grid-rows-auto">
      <section
        id="post-section"
        class="m-auto w-full md:w-3/4 col-span-6 md:col-span-4 md:row-span-1"
        v-if="currentPost !== ''"
      >
        <div
          v-html="currentPost.content.rendered"
          class="post-content-container"
        ></div>
      </section>
      <section
        class="m-auto w-full col-span-6 md:w-3/4 md:col-span-2 md:row-span-2"
      >
        <h2>Related Content:</h2>
        <p v-if="currentPost.title !== undefined">
          If you enjoyed {{ currentPost.title.rendered }}, we think you'll like:
        </p>
        <div
          class="related-content-container"
          v-for="relatedPost in relatedPosts"
          :key="relatedPost.id"
          :data-id="relatedPost.id"
        >
          <RelatedCard :post="relatedPost" />
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import RelatedCard from "@/components/RelatedCard.vue";
export default {
  name: "Post",
  components: { RelatedCard },
  data() {
    return {
      currentPost: "",
      id: this.$route.params.id,
      relatedPosts: [],
    };
  },
  computed: {
    postImage() {
      return this.currentPost.jetpack_featured_media_url;
    },
    ...mapState({
      baseAPIURL: (state) => state.baseAPIURL,
      posts: (state) => state.posts,
    }),
  },
  created() {
    fetch(`${this.baseAPIURL}/posts/${this.id}?_embed`)
      .then((resp) => resp.json())
      .then((post) => {
        this.currentPost = post;
      });
  },
  methods: {
    pushToRelated() {
      this.posts.forEach((post) => {
        post.relatedScore = 0;
        if (post.id !== this.currentPost.id) {
          post._embedded["wp:term"][0].forEach((el) => {
            for (
              let i = 0;
              i < this.currentPost._embedded["wp:term"][0].length;
              i++
            ) {
              if (
                el.name === this.currentPost._embedded["wp:term"][0][i].name
              ) {
                post.relatedScore = post.relatedScore + 3;
              }
            }
          });
          post._embedded["wp:term"][1].forEach((el) => {
            for (
              let i = 0;
              i < this.currentPost._embedded["wp:term"][1].length;
              i++
            ) {
              if (
                el.name === this.currentPost._embedded["wp:term"][1][i].name
              ) {
                post.relatedScore = post.relatedScore + 1;
              }
            }
          });
        }
      });
      this.relatedPosts = this.posts
        .sort((a, b) => a.relatedScore - b.relatedScore)
        .reverse()
        .slice(0, 5);
    },
  },
  watch: {
    currentPost: function () {
      if (this.posts.length > 0) {
        this.pushToRelated();
      }
    },
  },
};
</script>

<style lang="scss">
</style>
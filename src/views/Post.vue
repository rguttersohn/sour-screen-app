<template>
  <div class="post-container">
    <div v-if="currentPost !== ''">
      <img :src="postImage" />
      <h1 v-html="currentPost.title.rendered"></h1>
      <div
        v-html="currentPost.excerpt.rendered"
        class="post-excerpt-container"
      ></div>
      <div
        v-html="currentPost.content.rendered"
        class="post-content-container"
      ></div>
    </div>
    <div related-content-container>
      <h2>Related Content:</h2>
      <div v-for="post in relatedPosts" :key="post.id">
        <h2 v-html="post.title.rendered"></h2>
        <p v-html="post.excerpt.rendered"></p>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  data() {
    return {
      currentPost: "",
      id: this.$route.params.id,
      relatedPosts: [],
    };
  },
  computed: {
    postImage() {
      return this.currentPost._embedded["wp:featuredmedia"][0].media_details
        .sizes.full.source_url;
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
          post._embedded['wp:term'][0].forEach(el=>{
            for(let i  = 0;i < this.currentPost._embedded['wp:term'][0].length;i++){
              if (el.name === this.currentPost._embedded['wp:term'][0][i].name){
                post.relatedScore = post.relatedScore + 3
              }
            }
          })
          post._embedded['wp:term'][1].forEach(el=>{
            for(let i  = 0;i < this.currentPost._embedded['wp:term'][1].length;i++){
              if (el.name === this.currentPost._embedded['wp:term'][1][i].name){
                post.relatedScore = post.relatedScore + 1
              }
            }
          })
        }
      });
      this.relatedPosts = this.posts.sort((a,b)=>a.relatedScore - b.relatedScore).reverse().slice(0,5)
    },
  },
  watch: {
    currentPost: function () {
      this.pushToRelated();
    },
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
    font-family: Georgia, "Times New Roman", Times, serif;
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
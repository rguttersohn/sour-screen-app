<template>
  <div class="post-container" >
    <router-view name="PostContent">
      <h2>test</h2>
    </router-view>
    <div v-if="currentPost !== ''">
      <img :src="currentPost.jetpack_featured_media_url" />
      <!-- <h1 v-html="currentPost.title.rendered"></h1> -->
      <div
        v-html="currentPost.excerpt.rendered"
        class="post-excerpt-container"
      ></div>
      <div
        v-html="currentPost.content.rendered"
        class="post-content-container"
      ></div>
    </div>
    <section class="related-content">
       <h2>Related Content:</h2>
       <p v-if="currentPost.title !== undefined">If you enjoyed {{currentPost.title.rendered}}, we think you'll like:</p>
      <div class="related-content-container" v-for="relatedPost in relatedPosts" :key="relatedPost.id" :data-id="relatedPost.id">
          <RelatedCard :post='relatedPost' />
      </div>
    </section>
  </div>
</template>

<script>
import { mapState } from "vuex";
import RelatedCard from '@/components/RelatedCard.vue';
export default {
  name:"Post",
  components:{RelatedCard},
  data() {
    return {
      currentPost: "",
      id: this.$route.params.id,
      relatedPosts: []
    };
  },
  computed: {
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
    }
  },
  watch: {
   currentPost: function () {
     if (this.posts.length > 0){
      this.pushToRelated();
     }
    },
  }
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
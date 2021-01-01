<template>
  <div class="w-screen lg:py-36 py-24">
    <section class="w-full" id="intro-section" v-if="currentPost !== ''">
      <div class="w-9/12 m-auto">
        <div class="flex justify-start items-center">
          <h1
            v-html="currentPost.title.rendered"
            class="text-red-light mb-6 mr-5"
          ></h1>
          <div 
          @click="handleLike"
          class="like-button"></div>
          <template v-for="(tag, index) in currentPost._embedded['wp:term'][1]">
            <v-popover
              offset="16"
              placement="auto"
              hideOnTargetClick="false"
              :delay="{ show: 300, hide: 300 }"
              :key="index"
              v-if="tag.name === 'starter'"
            >
              <img
                class="tooltip-target"
                :src="starterIcon"
                alt="starter icon"
              />
              <template slot="popover">
                <img
                  :src="starterIcon"
                  alt="icon representing starter movies"
                />
                <p>
                  Movies with this icon are considered must-watch bad movies.
                </p>
              </template>
            </v-popover>
          </template>
        </div>
        <div class="mb-6 excerpt" v-html="currentPost.excerpt.rendered"></div>
      </div>
    </section>

    <div class="grid grid-cols-6 grid-rows-auto lg:gap-4 mx-5">
      <section
        id="post-section"
        class="m-auto w-full col-start-1 md:w-3/4 col-span-6 col-span-6 lg:col-span-4 row-span-1"
        v-if="currentPost !== ''"
      >
        <div
          v-html="currentPost.content.rendered"
          class="post-content-container"
        ></div>
      </section>
      <section
        class="m-auto w-full col-start-1 col-span-6 lg:col-start-5 lg:col-span-2 lg:row-span-2"
      >
        <h2 class="text-blue-light border-t-4 border-blue-light text-center">
          Related Content:
        </h2>
        <h4 class="text-center" v-if="currentPost.title !== undefined">
          If you enjoyed {{ currentPost.title.rendered }}, we think you'll like:
        </h4>
        <div
          class="flex flex-col md:flex-row md:justify-center md:flex-wrap lg:flex-col"
        >
          <div
            class="md:m-3 lg:my-auto"
            v-for="relatedPost in relatedPosts"
            :key="relatedPost.id"
            :data-id="relatedPost.id"
          >
            <RelatedCard :post="relatedPost" />
          </div>
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
      id: this.$route.params.id,
    }
  },
  computed: {
    postImage() {
      return this.currentPost.jetpack_featured_media_url;
    },
    starterIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/favorite-icon.svg`;
    },
    ...mapState({
      baseAPIURL: (state) => state.baseAPIURL,
      posts: (state) => state.posts,
      currentPost: (state) => state.currentPost,
      relatedPosts: (state) => state.relatedPosts,
    }),
  },
  created() {
    this.$store.dispatch("getCurrentPost", this.id);
  },
  methods:{
    handleLike(){
      fetch(`https://www.api-sourscreen.com/wp-json/v1/user_likes/${this.$store.state.userInfo.id}`,{
        method:"POST",
        body:JSON.stringify({
          post_id:this.currentPost.id,
          post_title:this.currentPost.title.rendered
        }),
         headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer" + this.$store.state.accessToken,
        },
      }).then(resp=>resp.json())
      .then(data=>console.log(data)) 
    },
  },
  watch: {
    posts() {
      if(this.posts.length > 0){
        this.$store.dispatch('pushToRelated')
      }
    },
    currentPost(){
      this.$store.dispatch('pushToRelated')
    }
  },
};
</script>

<style lang="scss">
#intro-section {
  .v-popover {
    position: initial;
  }

  .tooltip-target {
    width: 40px;
    max-width: inherit;
  }

  .tooltip.popover {
    background-color: white;
    padding: 3%;
    width: 20em;
    border-radius: 15px;
    border: 3px solid #0099dd;

    .popover-arrow {
      z-index: 99;
    }

    img {
      width: 5em;
      display: block;
      margin: 3% auto;
    }
  }

  .like-button{
    cursor: pointer;
  }
  .like-button::before{
      content:"+";
      font-size:1rem;
    }

  .fetch-button{
    cursor: pointer;
  }

  .fetch-button::before{
    content:"fetch";
    font-size:1rem;
  }
}
</style>
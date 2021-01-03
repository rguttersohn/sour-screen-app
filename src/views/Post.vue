<template>
  <div class="w-screen lg:py-36 py-24">
    <section class="w-full mb-5" id="intro-section" v-if="currentPost !== ''">
      <div class="w-9/12 m-auto">
        <div class="flex flex-col lg:flex-row justify-between items-center">
          <div id="post-title-container" class="w-full lg:w-3/4">
            <h1
              v-html="currentPost.title.rendered"
              class="text-red-light mb-6 mr-5"
            ></h1>
            <div
              class="mb-6 excerpt"
              v-html="currentPost.excerpt.rendered"
            ></div>
          </div>
          <div
          v-if="!this.currentPost.categories.includes(3)"
            id="post-icon-container"
            class="flex justify-evenly w-full lg:w-1/4"
          >
            <button
              v-if="this.$store.state.accessToken !== ''"
              @click="handleLike"
              class="bg-red-main hover:bg-blue-main text-white font-bold py-2 px-4 rounded flex items-center"
              :class="{ 'bg-red-light pointer-events-none': liked }"
              :disabled="liked"
            >
              <svg
                v-if="!liked"
                width="25px"
                height="25px"
                viewBox="0 0 198.995 198.996"
                xml:space="preserve"
              >
                <g>
                  <rect
                    y="80.097"
                    style="fill: white"
                    width="53.856"
                    height="113.267"
                  />
                  <rect
                    x="43.171"
                    y="86.167"
                    style="fill: white"
                    width="53.882"
                    height="14.16"
                  />
                  <rect
                    x="94.237"
                    y="51.007"
                    style="fill: white"
                    width="14.157"
                    height="36.863"
                  />
                  <rect
                    x="101.316"
                    y="15.376"
                    style="fill: white"
                    width="14.16"
                    height="36.864"
                  />
                  <rect
                    x="141.724"
                    y="15.376"
                    style="fill: white"
                    width="14.158"
                    height="56.368"
                  />
                  <rect
                    x="184.832"
                    y="85.902"
                    style="fill: white"
                    width="14.163"
                    height="97.723"
                  />
                  <rect
                    x="119.299"
                    y="183.625"
                    style="fill: white"
                    width="65.533"
                    height="14.151"
                  />
                  <rect
                    x="48.998"
                    y="169.462"
                    style="fill: white"
                    width="70.301"
                    height="14.163"
                  />
                  <rect
                    x="112.705"
                    y="1.218"
                    style="fill: white"
                    width="29.019"
                    height="14.158"
                  />
                  <rect
                    x="155.882"
                    y="71.745"
                    style="fill: white"
                    width="28.95"
                    height="14.158"
                  />
                </g>
              </svg>
              <h4 v-if="!liked" class="font-mono">Like this movie</h4>
              <h4 v-else class="font-mono">You liked this movie</h4>
            </button>
            <router-link
              class="font-mono text-red-main"
              :to="{ name: 'CreateAccount' }"
              v-else
            >
              <button
                class="bg-blue-main text-white font-bold py-2 px-4 rounded flex"
              >
                <h3>Log in/sign up</h3>
              </button>
            </router-link>
            <template
              v-for="(tag, index) in currentPost._embedded['wp:term'][1]"
            >
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
        </div>
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
      liked: false,
    };
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
      userLikes: (state) => state.userInfo.likes,
    }),
  },
  created() {
    this.$store.dispatch("getCurrentPost", this.id).then(()=>{
    this.checkIfLiked()});
  },
  methods: {
    handleLike() {
      fetch(
        `https://www.api-sourscreen.com/wp-json/v1/user_likes/${this.$store.state.userInfo.id}`,
        {
          method: "POST",
          body: JSON.stringify({
            post_id: this.currentPost.id,
            post_title: this.currentPost.title.rendered,
          }),
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer" + this.$store.state.accessToken,
          },
        }
      )
        .then((resp) => resp.json())
        .then(() => (this.liked = true));
    },
    checkIfLiked() {
      if (this.userLikes.length > 0) {
        for (let i = 0; i < this.userLikes.length; i++) {
          if (this.userLikes[i].post_id === this.currentPost.id) {
            this.liked = true;
            break
          } else {
            this.liked = false;
          }
        }
      }
    },
  },
  watch: {
    posts() {
      if (this.posts.length > 0) {
        this.$store.dispatch("pushToRelated");
      }
    },
    currentPost() {
      this.$store.dispatch("pushToRelated");
      this.checkIfLiked()
    },
    userLikes(){
      this.checkIfLiked()
    },
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
}
</style>
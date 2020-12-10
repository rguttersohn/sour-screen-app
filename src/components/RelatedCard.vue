<template>
  <router-link :to="{ name: 'Post', params: { id: post.id } }">
    <div class="related-card overflow-hidden md:w-64 lg:w-full lg:my-3">
      <div
        class="h-72 bg-cover border-4 rounded-xl border-blue-light hover:border-red-light relative md:w-64 lg:w-full"
        :style="{ backgroundImage: `url(${postImage})` }"
      >
        <h2
          class="text-center absolute top-56 text-white p-5 bg-gray-400 bg-opacity-50 md:hidden"
          v-html="post.title.rendered"
        ></h2>
              <template v-for="(tag, index) in post._embedded['wp:term'][1]">
        <v-popover
          offset="16"
          placement="auto"
          hideOnTargetClick="false"
          :delay="{ show: 300, hide: 300 }"
          :key="index"
          v-if="tag.name === 'starter'"
        >
          <img class="tooltip-target" :src="starterIcon" alt="starter icon" />
          <template slot="popover">
            <img :src="starterIcon" alt="icon representing starter movies" />
            <p>Movies with this icon are considered must-watch bad movies.</p>
          </template>
        </v-popover>
      </template>
      </div>
      <h3 class="hidden md:block text-center" v-html="post.title.rendered + ':' + post.excerpt.rendered"></h3>
      <h2 class=" md:hidden text-left text-blue-main" v-html="post.excerpt.rendered"></h2>
    </div>
  </router-link>
</template>

<script>
export default {
  props: {
    post: Object,
  },
  computed: {
    postImage() {
      return this.post.jetpack_featured_media_url;
    },
    starterIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/favorite-icon.svg`;
    },
  },
};
</script>

<style lang='scss'>

.related-card {
  .v-popover {
    z-index: 40;
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

    img {
      width: 5em;
      display: block;
      margin: 3% auto;
    }
  }
}
</style>

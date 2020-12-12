<template>
  <router-link :to="{ name: 'Post', params: { id: post.id } }">
    <div
      class="post-card h-60 bg-cover bg-center relative"
      :style="{ backgroundImage: `url(${postImage})` }"
    >
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
    <h3 
    class="text-center"
    v-html="post.title.rendered" 
    ></h3>
  </router-link>
</template>

<script>
export default {
  props: {
    post: Object,
  },
  data() {
    return {
      image: "",
    };
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
#database-list {
  .post-card {
    width: 250px;
  }

  .post-card {
    @apply mx-0;
    @apply h-60;
    @apply border;
  }

  .v-popover {
    position: absolute;
    bottom: 0rem;
    left: 80%;
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

    .movie-title {
    }
  }
}

#lists-list {
  .v-popover {
    position: absolute;
    bottom: 9rem;
    left: 90%;
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

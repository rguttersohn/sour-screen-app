<template>
  <router-link
    @mouseover.native="mousedOver = true"
    @mouseleave.native="mousedOver = false"
    :to="{ name: 'Post', params: { id: post.id } }"
  >
    <div class="home-card">
      <div class="home-card-upper">
        <img
          class="home-card-image"
          v-if="
            post._embedded['wp:featuredmedia'][0].media_details.sizes
              .medium_large !== undefined
          "
          :src="postImageML"
          alt=""
        />
        <img class="home-card-image" v-else :src="postImage" alt="" />
        <template v-for="(tag, index) in post._embedded['wp:term'][1]">
          <v-popover
            offset="16"
            placement="auto"
            hideOnTargetClick="false"
            :delay="{show:1000,hide:1000}"
            :key="index"
            v-if="tag.name === 'starter'"
          >
            <img
              class="starter-icon tooltip-target"
              :src="starterIcon"
              alt="starter icon"
            />
            <template slot="popover">
              <img :src="starterIcon" alt="icon representing starter movies">
              <p>Movies with this icon are considered must-watch bad movies.</p>
            </template>
          </v-popover>
        </template>
      </div>
      <div class="home-card-lower">
        <h2 v-html="post.title.rendered"></h2>
        <p v-html="post.excerpt.rendered"></p>
        <img
          v-if="mousedOver"
          class="play-icon-container"
          :src="readIcon"
          alt="read icon"
        />
      </div>
    </div>
  </router-link>
</template>

<script>
export default {
  props: {
    post: Object,
  },
  data() {
    return {
      mousedOver: false,
    };
  },
  computed: {
    postImageML() {
      return this.post._embedded["wp:featuredmedia"][0].media_details.sizes
        .medium_large.source_url;
    },
    postImage() {
      return this.post._embedded["wp:featuredmedia"][0].media_details.sizes.full
        .source_url;
    },
    readIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/read-icon-red.svg`;
    },
    starterIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/starter-icon.svg`;
    },
  },
  methods: {
    revealTooltip(event) {
      this.$store.commit("SET_ICON_TOOLTIP_COORDS", {
        x: event.clientX,
        y: event.clientY,
      });
      this.$store.commit("ICON_HOVER_TRUE");
    },
    hideTooltip() {
      this.$store.commit("ICON_HOVER_FALSE");
    },
  },
};
</script>

<style lang='scss'>
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.tooltip.popover {
  background-color: white;
  padding:3%;

  img{
    width:20px;
    display:block;
    margin:auto;
  }
}

.home-card-container {
  width: 30%;
  background-color: white;
  border: 5px solid $color-blue;
  border-radius: 15px;

  a {
    text-decoration: none;
    .home-card {
      width: 100%;
      height: 25rem;
      margin: 3% 1%;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: border-color 0.3s ease-in-out;
      border: 5px solid $color-lightblue;
      border-radius: 15px;

      &:hover,
      &:active,
      &:focus {
        border-color: $color-red;
      }

      .home-card-upper {
        position: relative;

        .v-popover {
          position: absolute;
          right: 10px;
          bottom: 190px;

          .starter-icon {
            width: 40px;
          }
          .v-popover-inner {
            background-color: white;
          }
        }

        .home-card-image {
          width: 100%;
          height: 250px;
          object-fit: cover;
          object-position: 0 10%;
          transform: scale(1.1, 1.1);
        }
      }

      .home-card-lower {
        padding: 3%;
        background-color: white;
        height: 100rem;
        position: relative;

        h2,
        p {
          margin: 0 0 2%;
        }
        h2 {
          text-align: left;
          color: $color-red;
        }
        .play-icon-container {
          display: block;
          position: absolute;
          height: 50%;
          top: 90px;
        }
      }
    }
  }
}
</style>

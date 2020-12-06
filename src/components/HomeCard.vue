<template>
  <router-link
    @mouseover.native="mousedOver = true"
    @mouseleave.native="mousedOver = false"
    :to="{ name: 'Post', params: { id: post.id } }"
  >
    <div class="border-4 rounded-2xl border-red-light hover:border-red-main overflow-hidden h-96">
      <div class="h-2/4 overflow-hidden">
        <img
          class="object-cover w-full"
          v-if="
            post._embedded['wp:featuredmedia'][0].media_details.sizes
              .medium_large !== undefined
          "
          :src="postImageML"
          alt=""
        />
        <img class="object-cover w-full" v-else :src="postImage" alt="" />
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
      <div class="h-2/4 p-3 bg-white relative">
        <h2 
        class="text-blue-main"
        v-html="post.title.rendered"></h2>
        <h4 v-html="post.excerpt.rendered"></h4>
        <img
          class="w-24 absolute  top-28"
          :class="mousedOver ? 'block' : 'hidden'"
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

.tooltip.popover {
  background-color: white;
  padding:3%;

  img{
    width:20px;
    display:block;
    margin:auto;
  }
}

</style>

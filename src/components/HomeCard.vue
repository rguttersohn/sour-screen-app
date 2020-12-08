<template>
  <router-link
    @mouseover.native="mousedOver = true"
    @mouseleave.native="mousedOver = false"
    :to="{ name: 'Post', params: { id: post.id } }"
  >
    <div class="border-4 rounded-2xl border-red-light hover:border-red-main overflow-hidden md:home-card-height h-96" >
      <!-- upper card -->
      <div class="h-2/4 bg-cover bg-top"
      :style="{backgroundImage:`url(${postImage})`}"
      >
      </div>
      <!-- lower card -->
      <div class="h-2/4 p-3 bg-white relative">
        <h2 
        class="text-blue-main"
        v-html="post.title.rendered"></h2>
        <h4 v-html="post.excerpt.rendered"></h4>
        <img
          class="w-24"
          :class="mousedOver ? 'block' : 'hidden'"
          :src="readIcon"
          alt="read icon"
        />
                <template v-for="(tag, index) in post._embedded['wp:term'][1]">
          <v-popover
            offset="16"
            placement="auto"
            hideOnTargetClick="false"
            :delay="{show:300,hide:300}"
            :key="index"
            v-if="tag.name === 'starter'"
          >
            <img
              class="tooltip-target"
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
    postImage(){
      return this.post.jetpack_featured_media_url
    },
    readIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/read-icon-red.svg`;
    },
    starterIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/favorite-icon.svg`;
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

.v-popover{
  position: absolute;
  bottom:0em;
  left:82%;
  z-index: 40;
}

.tooltip-target{
  width:40px;
  max-width:inherit;
}

.tooltip.popover {
  background-color: white;
  padding:3%;
  width:20em;
  border-radius:15px;
  border: 3px solid #0099dd;
  

  img{
    width:5em;
    display:block;
    margin:3% auto;
  }
}

</style>

<template>
  <div
    @mouseover="maintainTooltipReveal"
    @mouseout="hideTooltip"
    class="starter-icon-tooltip"
    :class="{ 'reveal-tooltip': iconHover }"
    :style="{ left: clientX - 200 + 'px', top: clientY + 50 + 'px' }"
  >
    <p>
      This is a Sour Screen starter. We think this is a must-watch bad movie.
    </p>
    <p>Check out our starter-list here:</p>
    <router-link
      @click.native="hideTooltip"
      class="starter-icon-button"
      :to="{ name: 'Post', params: { id: 23 } }"
    >
      <img
        class="get-started-icon-container"
        :src="getStartedIcon"
        alt="icon for getting started"
      />
    </router-link>
  </div>
</template>

<script>
export default {
  computed: {
    iconHover() {
      return this.$store.state.iconHover;
    },
    clientX() {
      return this.$store.state.iconTooltipX;
    },
    clientY() {
      return this.$store.state.iconTooltipY;
    },
    getStartedIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/get-started-icon.svg`;
    },
  },
  methods: {
    maintainTooltipReveal() {
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

.starter-icon-tooltip {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 999;
  line-height: 150%;
  font-weight: bold;
  background-color: white;
  padding: 1%;
  border-radius: 15px;
  width: 200px;
  transition: opacity 0.3s ease-in-out;
  transition-delay: 0.3s;
  opacity: 0;

  .home-banner-button {
    background-color: $color-red;
    color: white;
    padding: 3%;
    border-radius: 15px;
    text-decoration: none;
    transition: background-color 0.3s ease-in-out;
    top: 70%;
    text-transform: uppercase;

    .get-started-icon-container {
      width: 10rem;
    }
  }
}

.reveal-tooltip {
  opacity: 1;
}

@media screen and (max-width: 670px) {
  .starter-icon-tooltip {
    display: none !important;
  }
}
</style>
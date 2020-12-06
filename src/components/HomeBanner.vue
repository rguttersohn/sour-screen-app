<template>
  <div class="home-banner h-96 w-screen bg-fixed bg-top bg-no-repeat bg-cover">
    <div class="home-banner-content-container w-9/12 m-auto relative">
      <h1 class="text-white">Our Ultimate List of Bad Movies</h1>
      <h3 class="text-white">
        Looking to dip your toes into the waters of bad movies?<br />Check out
        our ultimate list of movies sure to make you chuckle.
      </h3>
      <router-link
        class="banner-button banner-button-position"
        :to="{ name: 'Post', params: { id: 23 } }"
      >
      <h2 class="font-mono text-white font-bold">Start here</h2>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    getStartedIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/get-started-icon.svg`;
    },
  },
};
</script>

<style lang="scss">

.home-banner {
  background: url(https://www.api-sourscreen.com/wp-content/uploads/2020/11/the-room.jpg);
  translate: scale(1.1, 1.1);

  .home-banner-content-container {
    h1 {
      position: absolute;
      top: 5rem;
    }

    h3 {
      position: absolute;
      top: 10rem;
    }

    .banner-button-position {
      position: absolute;
      top: 15rem;
    }
  }
}
</style>
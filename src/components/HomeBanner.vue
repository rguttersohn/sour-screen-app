<template>
  <div class="home-banner h-96 w-screen bg-auto bg-scroll md:bg-fixed bg-center md:bg-top md:bg-cover bg-no-repeat">
    <div class="home-banner-content-container py-3 lg:w-9/12 w-11/12 m-auto">
      <h1 class="text-white mb-12">Our Ultimate List of Bad Movies</h1>
      <h3 class="text-white mb-12">
        Looking to dip your toes into the waters of bad movies?<br />Check out
        our ultimate list of movies sure to make you chuckle.
      </h3>
      <router-link
        class="banner-button"
        :to="{ name: 'Post', params: { id: 23 } }"
      >
      <h2>Start here</h2>
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
}
</style>
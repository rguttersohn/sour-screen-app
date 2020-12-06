<template>
  <div class="home-banner h-96 w-screen bg-fixed bg-top bg-no-repeat bg-cover">
    <div class="home-banner-content-container w-9/12 m-auto relative">
      <h1 class="text-white">Our Ultimate List of Bad Movies</h1>
      <h3 class="text-white">
        Looking to dip your toes into the waters of bad movies?<br />Check out
        our ultimate list of movies sure to make you chuckle.
      </h3>
      <router-link
        class="home-banner-button bg-red-main p-8 rounded-xl"
        :to="{ name: 'Post', params: { id: 23 } }"
      >
        <img class="w-5" :src="getStartedIcon" alt="icon for getting started" />
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
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

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

    .home-banner-button {
      position: absolute;
      top: 15rem;

      img{
        width:10rem
      }
    }
  }
}
</style>
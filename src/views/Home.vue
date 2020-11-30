<template>
  <div class="home-container">
    <section id="home-banner-container">
      <HomeBanner />
    </section>
    <section id="new-movies-section">
      <h1>Latest additions to our database:</h1>
      <div class="home-card-flex-wrapper">
        <div
          class="home-card-container"
          v-for="movie in newMovies"
          :key="movie.id"
        >
          <HomeCard :post="movie" />
        </div>
      </div>
    </section>
    <section id="new-lists-container">
      <h1>Our latest lists:</h1>
      <div class="home-card-flex-wrapper">
        <div
          class="home-card-container"
          v-for="list in newLists"
          :key="list.id"
        >
          <HomeCard :post="list" />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import HomeCard from "@/components/HomeCard.vue";
import HomeBanner from "@/components/HomeBanner.vue";
export default {
  components: {
    HomeCard,
    HomeBanner,
  },
  computed: mapGetters(["newMovies", "newLists"]),
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

#home-banner-container {
  width: 100%;
  margin: auto;
}

.home-container {
  width:100vw;
  
  #new-movies-section {
    h1 {
      text-align: left;
      color: $color-blue;
    }
  }
  .home-card-flex-wrapper {
    display: flex;
    justify-content: space-around;
    width: 75%;
    margin: auto;
  }
}
</style>

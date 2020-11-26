<template>
  <div class="home">
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
export default {
  components: {
    HomeCard,
  },
  computed: mapGetters(["newMovies", "newLists"]),
};
</script>

<style lang="scss">
.home-card-flex-wrapper {
  display: flex;
  justify-content: space-around;
  width: 75%;
  margin: auto;

}
</style>

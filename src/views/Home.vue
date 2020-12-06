<template>
  <div class="w-screen lg:py-36 py-24 h-screen">
    <section id="home-banner-container">
      <HomeBanner />
    </section>
    <section>
      <h1>Why Bad Movies?</h1>
    </section>
    <section id="new-movies-section" class="w-screen my-5">
      <div class="new-movies-container bg-red-xLight py-6 w-screen">
         <h1 class="text-red-main text-center md:text-left md:ml-52  my-5 relative">
            Latest movies added to our database:
          </h1>
        <div class="flex justify-evenly flex-col md:flex-row w-full md:w-3/4 m-auto">
          <div
            class="home-card-container w-full md:w-1/3 md:mx-4 my-2 md:my-0"
            v-for="movie in newMovies"
            :key="movie.id"
          >
            <HomeCard :post="movie" />
          </div>
        </div>
      </div>
    </section>
    <section id="new-lists-section" class="w-screen my-5">
      <div class="new-lists-container bg-blue-xLight py-6 w-screen">
        <h1 class="text-center md:text-right md:mr-52 my-5 text-blue-main">Our latest lists:</h1>
        <div class="flex justify-evenly w-full flex-col md:flex-row md:w-3/4 m-auto">
          <div
            class="home-card-container w-full  md:w-1/3  md:mx-4 md:h-96"
            v-for="list in newLists"
            :key="list.id"
          >
            <HomeCard :post="list" />
          </div>
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

</style>

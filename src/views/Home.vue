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
         <h1 class="text-red-main text-left ml-52  my-5 relative">
            Latest additions to our database:
          </h1>
        <div class="flex justify-evenly w-3/4 m-auto">
          <div
            class="home-card-container w-1/3 border-4 rounded-2xl border-red-light"
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
        <h1 class="text-right mr-52 my-5 text-blue-main">Our latest lists:</h1>
        <div class="flex justify-evenly w-3/4 m-auto">
          <div
            class="home-card-container w-1/3 border-4 rounded-2xl border-blue-light"
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
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;
$color-red-background: #fdf2fd;
$color-blue-background: #f1f3fd;

.home-container {
  width: 100vw;

  #home-banner-container {
    width: 100%;
    margin: auto;
  }

  #new-movies-section {
    width: 100%;
    background-color: $color-red-background;
    padding: 1% 0;
    .new-movies-container {
      width: 75%;
      margin: 3% auto;
      h1 {
        text-align: left;
        color: $color-blue;
      }
    }
  }
  #new-lists-section {
    width: 100%;
    padding: 1% 0;
    .new-lists-container {
      width: 75%;
      margin: 3% auto;
      h1 {
        text-align: right;
        color: $color-red;
      }
    }
  }
}
</style>

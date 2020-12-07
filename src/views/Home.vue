<template>
  <div class="w-screen lg:py-36 py-24 h-screen">
    <section id="home-banner-container">
      <HomeBanner />
    </section>
    <section class="w-screen my-5 py-6 lg:section-height">
      <div class="lg:w-3/4 w-full p-6 m-auto h-full">
        <h1 class="text-center text-red-main mb-10">Why Bad Movies?</h1>
        <div class="flex lg:flex-row  flex-col-reverse md:justify-space-evenly h-3/4">
          <div class=""><h4 class="mb-10">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptates, aliquam voluptatem. Assumenda, harum. Alias, optio maiores! Fuga sit harum doloremque? Repellat numquam et, eos nemo sit animi tenetur sunt officiis.</h4>
          <h4 class="my-10">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis eveniet doloremque quae mollitia tempora reprehenderit nulla consequuntur, blanditiis laudantium cumque nesciunt architecto nemo magnam veniam, maiores libero ipsam. Quaerat, incidunt.</h4></div>
            <img class="w-2/4 hidden lg:block" :src="vampireIcon" alt="vampire/dracula illustration">
        </div>
          
      </div>
    </section>
    <section id="new-lists-section" class="w-screen my-5 lg:section-height">
      <div class="new-movies-container bg-blue-xLight py-6 w-screen h-full">
        <h1 class="text-center lg:text-right lg:mr-52 my-5 text-blue-main">
          Our latest lists:
        </h1>
        <!-- flex container -->
        <div
          class="home-card-container"
        >
         <!-- flex item -->
          <div
             class="home-card-item"
            v-for="list in newLists"
            :key="list.id"
          >
            <HomeCard :post="list" />
          </div>
        </div>
      </div>
    </section>
    <section id="new-movies-section" class="w-screen my-5 lg:section-height">
      <div class="new-movies-container bg-red-xLight py-6 w-screen h-full">
        <h1
          class="text-red-main text-center lg:text-left lg:ml-52 my-5 relative"
        >
          Latest movies added to our database:
        </h1>
        <!-- flex container -->
        <div
          class="home-card-container"
        >
        <!-- flex item -->
          <div
          class="home-card-item"
            v-for="movie in newMovies"
            :key="movie.id"
          >
            <HomeCard :post="movie" />
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
  computed:{
    vampireIcon(){
      return 'https://www.api-sourscreen.com/wp-content/uploads/2020/12/vampire.svg'
    },
   ...mapGetters(["newMovies", "newLists"])
  } ,
};
</script>

<style lang="scss">

.home-card-container{
  @apply flex justify-evenly flex-col lg:flex-row w-full md:w-3/4 m-auto
}

.home-card-item{
  @apply flex-1 mx-5 md:mx-20 lg:mx-5
}
</style>

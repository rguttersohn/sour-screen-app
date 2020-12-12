<template>
  <div class="w-screen py-24 lg:py-36 h-full" id="database-list">
    <div class=" w-full md:w-11/12 lg:w-3/4 m-auto my-10">
      <h1 class="text-center text-red-main my-10">Movies in our database</h1>
      <section id="action-posts">
        <h2 class="text-center text-blue-main mt-10 mb-5">Action</h2>
        <div class="flex flex-row overflow-x-scroll post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in action"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>
      <section id="christian-posts">
        <h2 class="text-center text-blue-main mt-10 mb-5">Christian</h2>
        <div class="flex flex-row overflow-x-scroll post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in christian"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>
      <section id="horror-posts">
        <h2 class="text-center text-blue-main mt-10 mb-5">Horror</h2>
        <div class="flex flex-row overflow-x-scroll post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in horror"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>
      <section id="sports-posts">
        <h2 class="text-center text-blue-main mt-10 mb-5">Sports</h2>
        <div class="flex flex-row overflow-x-scroll post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in sports"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>

      <section id="christmas-posts">
        <h2 class="text-center text-blue-light mt-10 mb-5">Christmas</h2>
        <div class="flex flex-row overflow-x-scroll post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in christmas"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>

    </div>
  </div>
</template>

<script>
import PostCard from "@/components/PostCard.vue";
import { mapGetters } from "vuex";
export default {
  components: {
    PostCard,
  },
  computed: {
    movies() {
      return this.$store.getters.movies;
    },
    ...mapGetters(["action", "christian", "christmas", "horror", "sports"]),
  },
};
</script>

<style scoped>


.post-card-container {
  @apply bg-red-xLight shadow-inner h-80 m-auto w-11/12;
}

section{
  @apply my-20 w-full;
}
</style>
<template>
  <div class="w-screen py-24 lg:py-36 h-full" id="database-list">
    <div class="w-full md:w-11/12 lg:w-3/4 m-auto my-10">
      <h1 class="text-center text-red-main my-10">Movies in our database</h1>
      <section id="action-posts">
        <h1 class="text-blue-main mt-10 mb-5">Action</h1>
        <div class="post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in action"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>

      <section id="drama-posts">
        <h1 class="text-blue-main mt-10 mb-5">Drama</h1>
        <div class="post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in drama"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>

      <section id="christian-posts">
        <h1 class="text-blue-main mt-10 mb-5">Christian</h1>
        <div class="post-card-container">
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
        <h1 class="text-blue-main mt-10 mb-5">Horror</h1>
        <div class=" post-card-container">
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
        <h1 class="text-blue-main mt-10 mb-5">Disney Channel Originals</h1>
        <div class="post-card-container">
          <div
            class="flex-shrink-0"
            v-for="movie in disneyChannel"
            :key="movie.id"
          >
            <PostCard :post="movie" />
          </div>
        </div>
      </section>

      <section id="christmas-posts">
        <h1 class="text-blue-main mt-10 mb-5">Christmas</h1>
        <div class="post-card-container">
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
  name:'Movies',
  components: {
    PostCard,
  },
  computed: {
    movies() {
      return this.$store.getters.movies;
    },
    ...mapGetters(["action", "christian", "christmas", "horror", "disneyChannel","drama"]),
  },
};
</script>

<style scoped>


.post-card-container {
  @apply bg-red-xLight w-full flex flex-row items-center overflow-x-scroll shadow-inner h-80 m-auto w-11/12 px-5;
}

section{
  @apply my-20 w-full;
}
</style>
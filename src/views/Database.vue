<template>
  <div class="w-screen py-24 lg:py-36 h-screen" id="database-list">
    <div class="w-3/4 m-auto">
      <h1 class="text-center text-red-main">Movies in our database</h1>
       <div 
        class="border-4 border-red-main rounded-lg w-full md:w-3/4 m-auto  h-64 my-3 overflow-hidden"
        v-for="movie in movies" 
          :key="movie.id">
            <PostCard :post="movie" />
        </div>
    </div>
  </div>
</template>

<script>
import PostCard from '@/components/PostCard.vue';
export default {
    components:{
        PostCard
    },
    computed:{
      movies(){
        return this.$store.getters.movies
      }
    },
};
</script>

<style lang="sass" scoped>
</style>
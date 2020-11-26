<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="movie in movies" :key="movie.id">
          <PostCard :post="movie" />
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
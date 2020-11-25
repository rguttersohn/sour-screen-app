<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="movie in movies" :key="movie.id">
        <router-link :to="{name:'Post', params:{id:movie.id}}">
          <PostCard :post="movie" />
        </router-link>
      </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import PostCard from '@/components/PostCard.vue';
export default {
    components:{
        PostCard
    },
    computed:mapState({
        movies:state=>state.movies
    }),
    created:function(){
        this.$store.dispatch('getPosts')
    }
};
</script>

<style lang="sass" scoped>
</style>
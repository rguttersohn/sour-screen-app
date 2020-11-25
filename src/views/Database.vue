<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="movie in movies" :key="movie.id">
        <router-link :to="{name:'Post', params:{id:movie.id}}">
          <div>
              <h3 v-if="movie.categories[0] === 4">{{movie.title.rendered}}</h3>
              <div v-if="movie.categories[0]=== 4" v-html="movie.excerpt.rendered"></div>
          </div>
        </router-link>
      </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
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
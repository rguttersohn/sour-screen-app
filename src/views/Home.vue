<template>
  <div class="home">
    <div class="home-card-container" v-for="movie in newMovies" :key="movie.id">
      <HomeCard :post="movie"/>
    </div>
    <div class="home-card-container" v-for="list in newLists" :key="list.id">
      <HomeCard :post="list"/>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import HomeCard from '@/components/HomeCard.vue';
export default {
  components:{
HomeCard
  },
  computed:mapGetters(['newMovies','newLists'])
}
</script>

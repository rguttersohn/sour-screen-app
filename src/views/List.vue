<template>
  <div>
    <h2>Our lists:</h2>
    <div v-for="list in lists" :key="list.id">
      <router-link :to="{name:'Post',params:{id:post.id}}" >
            <h3 v-if="list.categories[0] === 3">{{list.title.rendered}}</h3>
            <p  v-if="list.categories[0] === 3" v-html="list.excerpt.rendered"></p>
      </router-link>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  computed: mapState({
    lists:state=>state.lists
  })
};
</script>

<style lang="sass" scoped>
</style>

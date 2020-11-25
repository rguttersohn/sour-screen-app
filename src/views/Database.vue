<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="post in posts" :key="post.id">
        <router-link :to="{name:'Post', params:{id:post.id}}">
          <p v-if="post.categories[0] === 4">{{post.title.rendered}}</p>
        </router-link>
      </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    computed:mapState({
        posts:state=>state.posts
    }),
    created:function(){
      this.$store.dispatch('getPosts')
    }
};
</script>

<style lang="sass" scoped>
</style>
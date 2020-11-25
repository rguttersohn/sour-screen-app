<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="post in posts" :key="post.id">
        <router-link :to="{name:'Post', params:{id:post.id}}">
          <div>
              <h3 v-if="post.categories[0] === 4">{{post.title.rendered}}</h3>
              <div v-if="post.categories[0]=== 4" v-html="post.excerpt.rendered"></div>
          </div>
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